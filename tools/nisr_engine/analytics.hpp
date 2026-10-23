#pragma once
#include <string>
#include <vector>
#include <map>
#include <set>
#include <fstream>
#include <sstream>
#include <cmath>
#include <algorithm>
#include <unordered_map>
#include <stdexcept>

namespace saip {

/* ══════════════════════════════════════════════════════════
   CSV DATA STRUCTURE
   ══════════════════════════════════════════════════════════ */
struct CsvData {
    std::vector<std::string> headers;
    std::vector<std::vector<std::string>> rows;
    std::unordered_map<std::string, int> headerIndex;

    int columnIndex(const std::string& name) const {
        auto it = headerIndex.find(name);
        return it == headerIndex.end() ? -1 : it->second;
    }

    bool isNumeric(int colIdx, size_t sampleSize = 100) const {
        size_t checked = 0, numeric = 0;
        for (const auto& row : rows) {
            if (checked >= sampleSize) break;
            if (colIdx >= (int)row.size()) continue;
            const std::string& v = row[colIdx];
            if (v.empty()) continue;
            try {
                size_t pos;
                std::stod(v, &pos);
                if (pos == v.size()) numeric++;
            } catch (...) {}
            checked++;
        }
        return checked > 0 && (double)numeric / checked >= 0.8;
    }
};

/* ══════════════════════════════════════════════════════════
   CSV PARSER (RFC-4180 with quotes)
   ══════════════════════════════════════════════════════════ */
inline CsvData parseCsv(const std::string& path) {
    std::ifstream file(path, std::ios::binary);
    if (!file.is_open())
        throw std::runtime_error("Cannot open CSV: " + path);

    CsvData data;
    std::string line;
    bool firstRow = true;

    while (std::getline(file, line)) {
        if (!line.empty() && line.back() == '\r') line.pop_back();
        if (line.empty() && firstRow) continue;

        std::vector<std::string> fields;
        std::string cell;
        bool inQuotes = false;

        for (size_t i = 0; i < line.size(); ++i) {
            char c = line[i];
            if (inQuotes) {
                if (c == '"') {
                    if (i + 1 < line.size() && line[i + 1] == '"') { cell += '"'; ++i; }
                    else inQuotes = false;
                } else cell += c;
            } else {
                if (c == '"') inQuotes = true;
                else if (c == ',') { fields.push_back(cell); cell.clear(); }
                else cell += c;
            }
        }
        fields.push_back(cell);

        if (firstRow) {
            for (size_t i = 0; i < fields.size(); ++i) {
                std::string h = fields[i];
                // Trim whitespace
                while (!h.empty() && (h.front() == ' ' || h.front() == '\t')) h.erase(0, 1);
                while (!h.empty() && (h.back() == ' ' || h.back() == '\t')) h.pop_back();
                data.headers.push_back(h);
                data.headerIndex[h] = (int)i;
            }
            firstRow = false;
        } else {
            while (fields.size() < data.headers.size()) fields.push_back("");
            data.rows.push_back(std::move(fields));
        }
    }
    return data;
}

/* ══════════════════════════════════════════════════════════
   PIVOT — matches NisrAnalytics::pivot() PHP return format
   ══════════════════════════════════════════════════════════ */
struct PivotResult {
    std::vector<std::string> rows;
    std::vector<std::string> columns;
    std::map<std::string, std::map<std::string, double>> data;
};

inline PivotResult pivot(
    const CsvData& csv,
    const std::string& rowField,
    const std::string& colField,
    const std::string& valField,
    const std::string& agg)
{
    int ri = csv.columnIndex(rowField);
    int ci = csv.columnIndex(colField);
    int vi = csv.columnIndex(valField);

    if (ri < 0 || ci < 0 || vi < 0)
        throw std::runtime_error("Invalid field in pivot: check column names");

    PivotResult result;
    std::map<std::string, int> rowCounter;
    std::set<std::string> allRows, allCols;

    for (const auto& row : csv.rows) {
        if ((size_t)ri >= row.size() || (size_t)ci >= row.size() || (size_t)vi >= row.size())
            continue;

        const std::string& rk = row[ri];
        const std::string& ck = row[ci];
        const std::string& raw = row[vi];
        if (rk.empty() || ck.empty()) continue;

        double v = 0.0;
        if (agg == "count") {
            v = 1.0;
        } else {
            try { v = std::stod(raw); }
            catch (...) { continue; }
        }

        auto& cell = result.data[rk][ck];

        if (agg == "sum" || agg == "avg" || agg == "count") {
            cell += v;
        } else if (agg == "min") {
            if (result.data[rk].find(ck) == result.data[rk].end() || v < cell) cell = v;
        } else if (agg == "max") {
            if (result.data[rk].find(ck) == result.data[rk].end() || v > cell) cell = v;
        }
        rowCounter[rk]++;
        allRows.insert(rk);
        allCols.insert(ck);
    }

    if (agg == "avg") {
        for (auto& [rk, rowMap] : result.data) {
            int n = std::max(1, rowCounter[rk]);
            for (auto& [ck, v] : rowMap) v /= n;
        }
    }

    result.rows.assign(allRows.begin(), allRows.end());
    result.columns.assign(allCols.begin(), allCols.end());
    return result;
}

/* ══════════════════════════════════════════════════════════
   QUALITY — matches NisrAnalytics::computeQuality() output
   ══════════════════════════════════════════════════════════ */
struct ColumnQuality {
    std::string column_name;
    std::string data_type;
    int total_values = 0;
    int missing_count = 0;
    double missing_pct = 0;
    int unique_count = 0;
    double min_value = 0, max_value = 0;
    double mean_value = 0, median_value = 0, std_dev = 0;
    int outlier_count = 0;
    bool has_min = false, has_max = false, has_mean = false, has_median = false, has_std = false;
};

inline ColumnQuality analyzeColumn(const CsvData& csv, int colIdx, const std::string& name) {
    ColumnQuality q;
    q.column_name = name;
    q.data_type = csv.isNumeric(colIdx) ? "number" : "string";
    q.total_values = (int)csv.rows.size();

    std::vector<double> numeric;
    std::set<std::string> uniques;

    for (const auto& row : csv.rows) {
        if (colIdx >= (int)row.size() || row[colIdx].empty()) {
            q.missing_count++;
            continue;
        }
        const std::string& v = row[colIdx];
        uniques.insert(v);

        if (q.data_type == "number") {
            try { numeric.push_back(std::stod(v)); } catch (...) {}
        }
    }

    q.unique_count = (int)uniques.size();
    q.missing_pct = q.total_values > 0
        ? (double)q.missing_count / q.total_values * 100.0 : 0.0;

    if (!numeric.empty()) {
        std::sort(numeric.begin(), numeric.end());
        q.min_value = numeric.front();  q.has_min = true;
        q.max_value = numeric.back();   q.has_max = true;

        double sum = 0, sqSum = 0;
        for (double v : numeric) { sum += v; sqSum += v * v; }
        double n = (double)numeric.size();
        q.mean_value = sum / n;         q.has_mean = true;

        double variance = (sqSum / n) - (q.mean_value * q.mean_value);
        q.std_dev = std::sqrt(std::max(0.0, variance));  q.has_std = true;

        q.median_value = numeric[numeric.size() / 2];    q.has_median = true;

        for (double v : numeric)
            if (q.std_dev > 0 && std::abs(v - q.mean_value) > 2 * q.std_dev)
                q.outlier_count++;
    }

    return q;
}

inline std::vector<ColumnQuality> quality(const CsvData& csv) {
    std::vector<ColumnQuality> out;
    for (size_t i = 0; i < csv.headers.size(); ++i)
        out.push_back(analyzeColumn(csv, (int)i, csv.headers[i]));
    return out;
}

/* ══════════════════════════════════════════════════════════
   TIMESERIES — matches NisrAnalytics::timeseries()
   ══════════════════════════════════════════════════════════ */
struct TimeSeriesPoint { std::string x; double y; };

struct TimeSeriesResult {
    std::vector<TimeSeriesPoint> points;
    std::vector<double> smoothed;
};

inline TimeSeriesResult timeseries(
    const CsvData& csv,
    const std::string& dateField,
    const std::string& metric,
    const std::string& agg,
    int smoothPeriods)
{
    int di = csv.columnIndex(dateField);
    int mi = csv.columnIndex(metric);
    if (di < 0 || mi < 0) throw std::runtime_error("Invalid field names");

    std::map<std::string, double> bucket;
    std::map<std::string, int> counter;

    for (const auto& row : csv.rows) {
        if ((size_t)di >= row.size() || (size_t)mi >= row.size()) continue;
        const std::string& d = row[di];
        if (d.empty()) continue;

        double v = 0;
        if (agg == "count") v = 1.0;
        else {
            try { v = std::stod(row[mi]); } catch (...) { continue; }
        }

        auto& slot = bucket[d];
        if (agg == "min") {
            if (counter[d] == 0 || v < slot) slot = v;
        } else if (agg == "max") {
            if (counter[d] == 0 || v > slot) slot = v;
        } else {
            slot += v;
        }
        counter[d]++;
    }

    if (agg == "avg") {
        for (auto& [k, v] : bucket) v /= std::max(1, counter[k]);
    }

    TimeSeriesResult result;
    for (auto& [k, v] : bucket) result.points.push_back({k, v});

    std::sort(result.points.begin(), result.points.end(),
        [](const TimeSeriesPoint& a, const TimeSeriesPoint& b) { return a.x < b.x; });

    if (smoothPeriods > 1 && !result.points.empty()) {
        size_t n = result.points.size();
        result.smoothed.resize(n, 0.0);
        double runningSum = 0;
        for (size_t i = 0; i < n; ++i) {
            runningSum += result.points[i].y;
            if (i >= (size_t)smoothPeriods)
                runningSum -= result.points[i - smoothPeriods].y;
            size_t window = std::min((size_t)smoothPeriods, i + 1);
            result.smoothed[i] = runningSum / window;
        }
    }

    return result;
}

/* ══════════════════════════════════════════════════════════
   COMPARE — matches NisrAnalytics::compare()
   ══════════════════════════════════════════════════════════ */
struct CompareResult {
    std::map<std::string, double> data;
    std::string first, second;
    double percent = 0;
};

inline CompareResult compare(
    const CsvData& csv,
    const std::string& dimension,
    const std::string& metric,
    const std::string& agg)
{
    auto p = pivot(csv, dimension, dimension, metric, agg);
    CompareResult result;

    bool firstEntry = true;
    double topVal = 0, bottomVal = 0;

    for (auto& [rk, rowMap] : p.data) {
        double v = 0;
        for (auto& [ck, val] : rowMap) { v = val; break; }
        result.data[rk] = v;

        if (firstEntry || v > topVal) { topVal = v; result.first = rk; }
        if (firstEntry || v < bottomVal) { bottomVal = v; result.second = rk; }
        firstEntry = false;
    }

    if (bottomVal != 0)
        result.percent = (topVal - bottomVal) / std::abs(bottomVal) * 100.0;

    return result;
}

} // namespace saip