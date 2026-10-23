#pragma once
#include "analytics.hpp"
#include <sstream>
#include <iomanip>

namespace saip {

inline std::string escapeJson(const std::string& s) {
    std::string out;
    out.reserve(s.size() + 8);
    for (char c : s) {
        switch (c) {
            case '"':  out += "\\\""; break;
            case '\\': out += "\\\\"; break;
            case '\n': out += "\\n";  break;
            case '\r': out += "\\r";  break;
            case '\t': out += "\\t";  break;
            default:
                if ((unsigned char)c < 0x20) {
                    char buf[8];
                    snprintf(buf, sizeof(buf), "\\u%04x", c);
                    out += buf;
                } else out += c;
        }
    }
    return out;
}

inline std::string num(double v) {
    if (std::isnan(v) || std::isinf(v)) return "null";
    std::ostringstream ss;
    ss << std::setprecision(15) << v;
    return ss.str();
}

/* Matches PHP: ['rows' => [...], 'columns' => [...], 'data' => [...]] */
inline std::string pivotToJson(const PivotResult& p) {
    std::ostringstream ss;
    ss << "{\"rows\":[";
    for (size_t i = 0; i < p.rows.size(); ++i) {
        if (i) ss << ",";
        ss << "\"" << escapeJson(p.rows[i]) << "\"";
    }
    ss << "],\"columns\":[";
    for (size_t i = 0; i < p.columns.size(); ++i) {
        if (i) ss << ",";
        ss << "\"" << escapeJson(p.columns[i]) << "\"";
    }
    ss << "],\"data\":{";
    bool firstRow = true;
    for (auto& [rk, rowMap] : p.data) {
        if (!firstRow) ss << ",";
        firstRow = false;
        ss << "\"" << escapeJson(rk) << "\":{";
        bool firstCell = true;
        for (auto& [ck, v] : rowMap) {
            if (!firstCell) ss << ",";
            firstCell = false;
            ss << "\"" << escapeJson(ck) << "\":" << num(v);
        }
        ss << "}";
    }
    ss << "}}";
    return ss.str();
}

/* Matches rows in `nisr_data_quality` table */
inline std::string qualityToJson(const std::vector<ColumnQuality>& q) {
    std::ostringstream ss;
    ss << "[";
    for (size_t i = 0; i < q.size(); ++i) {
        if (i) ss << ",";
        const auto& c = q[i];
        ss << "{"
           << "\"column_name\":\"" << escapeJson(c.column_name) << "\","
           << "\"data_type\":\"" << c.data_type << "\","
           << "\"total_values\":" << c.total_values << ","
           << "\"missing_count\":" << c.missing_count << ","
           << "\"missing_pct\":" << num(c.missing_pct) << ","
           << "\"unique_count\":" << c.unique_count << ","
           << "\"min_value\":"    << (c.has_min    ? num(c.min_value)    : "null") << ","
           << "\"max_value\":"    << (c.has_max    ? num(c.max_value)    : "null") << ","
           << "\"mean_value\":"   << (c.has_mean   ? num(c.mean_value)   : "null") << ","
           << "\"median_value\":" << (c.has_median ? num(c.median_value) : "null") << ","
           << "\"std_dev\":"      << (c.has_std    ? num(c.std_dev)      : "null") << ","
           << "\"outlier_count\":" << c.outlier_count
           << "}";
    }
    ss << "]";
    return ss.str();
}

/* Matches PHP: ['points' => [...], 'smoothed' => [...]] */
inline std::string timeseriesToJson(const TimeSeriesResult& t) {
    std::ostringstream ss;
    ss << "{\"points\":[";
    for (size_t i = 0; i < t.points.size(); ++i) {
        if (i) ss << ",";
        ss << "{\"x\":\"" << escapeJson(t.points[i].x)
           << "\",\"y\":" << num(t.points[i].y) << "}";
    }
    ss << "],\"smoothed\":[";
    for (size_t i = 0; i < t.smoothed.size(); ++i) {
        if (i) ss << ",";
        ss << num(t.smoothed[i]);
    }
    ss << "]}";
    return ss.str();
}

/* Matches PHP: ['data' => [...], 'delta' => ['first','second','percent']] */
inline std::string compareToJson(const CompareResult& c) {
    std::ostringstream ss;
    ss << "{\"data\":{";
    bool first = true;
    for (auto& [k, v] : c.data) {
        if (!first) ss << ",";
        first = false;
        ss << "\"" << escapeJson(k) << "\":" << num(v);
    }
    ss << "},\"delta\":{"
       << "\"first\":\"" << escapeJson(c.first) << "\","
       << "\"second\":\"" << escapeJson(c.second) << "\","
       << "\"percent\":" << num(c.percent)
       << "}}";
    return ss.str();
}

} // namespace saip