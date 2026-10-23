#include "csv_parser.hpp"
#include "analytics.hpp"
#include "json_bridge.hpp"
#include <iostream>
#include <string>

using namespace saip;

static void printUsage() {
    std::cerr <<
        "Usage:\n"
        "  nisr_engine pivot <csv> <rowField> <colField> <valField> [agg]\n"
        "  nisr_engine quality <csv>\n"
        "  nisr_engine timeseries <csv> <dateField> <metric> [agg] [smooth]\n"
        "  nisr_engine compare <csv> <dimension> <metric> [agg]\n"
        "  nisr_engine schema <csv>\n";
}

int main(int argc, char** argv) {
    if (argc < 3) { printUsage(); return 1; }

    std::string mode = argv[1];
    std::string path = argv[2];

    try {
        CsvData csv = parseCsv(path);

        if (mode == "pivot") {
            if (argc < 6) { printUsage(); return 1; }
            std::string row = argv[3], col = argv[4], val = argv[5];
            std::string agg = argc > 6 ? argv[6] : "sum";
            std::cout << pivotToJson(pivot(csv, row, col, val, agg));
        }
        else if (mode == "quality") {
            std::cout << qualityToJson(quality(csv));
        }
        else if (mode == "timeseries") {
            if (argc < 5) { printUsage(); return 1; }
            std::string date = argv[3], metric = argv[4];
            std::string agg = argc > 5 ? argv[5] : "sum";
            int smooth = argc > 6 ? std::stoi(argv[6]) : 0;
            std::cout << timeseriesToJson(timeseries(csv, date, metric, agg, smooth));
        }
        else if (mode == "compare") {
            if (argc < 5) { printUsage(); return 1; }
            std::string dim = argv[3], metric = argv[4];
            std::string agg = argc > 5 ? argv[5] : "sum";
            std::cout << compareToJson(compare(csv, dim, metric, agg));
        }
        else if (mode == "schema") {
            std::cout << "{\"headers\":[";
            for (size_t i = 0; i < csv.headers.size(); ++i) {
                if (i) std::cout << ",";
                std::cout << "{\"name\":\"" << escapeJson(csv.headers[i])
                          << "\",\"type\":\""
                          << (csv.isNumeric((int)i) ? "number" : "string") << "\"}";
            }
            std::cout << "],\"row_count\":" << csv.rows.size() << "}";
        }
        else {
            std::cerr << "Unknown mode: " << mode << "\n";
            printUsage();
            return 1;
        }
    } catch (const std::exception& e) {
        std::cout << "{\"error\":\"" << escapeJson(e.what()) << "\"}";
        return 1;
    }
    return 0;
}