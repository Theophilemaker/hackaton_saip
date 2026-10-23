<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card theory-hero">
            <div>
                <a href="<?= BASE_URL ?>/index.php?url=government" class="back-link">← Government Analytics</a>
                <h1>📖 NST2 & Vision 2050 — Theory & Strategy</h1>
                <p>Complete strategic framework with official indicators from MINECOFIN, NISR, and MINAGRI</p>
            </div>
            <div class="hero-meta">
                <span class="badge green">2024–2029</span>
                <span class="badge">Vision 2050</span>
            </div>
        </section>

        <!-- ============ INTRODUCTION ============ -->
        <section class="glass-card theory-intro">
            <div class="intro-grid">
                <div class="intro-text">
                    <h2>Rwanda's Second National Strategy for Transformation (NST2)</h2>
                    <p>
                        The NST2 (2024–2029) is Rwanda's five-year development blueprint that translates Vision 2050
                        aspirations into actionable priorities. It is organised around <strong>3 pillars</strong> and
                        <strong>4 enablers</strong>, with <strong>50+ outcome indicators</strong> tracked by NISR and
                        reported by MINECOFIN.
                    </p>
                    <p>
                        Aligned with the NST2, the agriculture sector under the Economic Transformation pillar
                        targets <strong>6.5% average annual growth</strong>, with the overall economy targeting
                        <strong>9.3% GDP growth</strong> by 2029.
                    </p>
                </div>
                <div class="intro-stats">
                    <div class="is-card">
                        <strong>9.3%</strong>
                        <small>Average annual GDP growth target (2024–2029)</small>
                    </div>
                    <div class="is-card">
                        <strong>6.5%</strong>
                        <small>Agriculture sector growth target</small>
                    </div>
                    <div class="is-card">
                        <strong>$1,369</strong>
                        <small>Per capita income target by 2029</small>
                    </div>
                    <div class="is-card">
                        <strong>$7.3B</strong>
                        <small>Total export target by 2029</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ PILLAR 1: ECONOMIC TRANSFORMATION ============ -->
        <section class="glass-card theory-pillar" style="--pillar-color: #22C55E;">
            <div class="pillar-header">
                <span class="pillar-icon-lg" style="background: linear-gradient(135deg, #16A34A, #22C55E);">📈</span>
                <div>
                    <h2>Pillar 1 — Economic Transformation</h2>
                    <p>Modern agriculture, industrialisation, export-led growth, digital economy</p>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">1.1</span>
                    Modern Agriculture & Productivity
                    <span class="sh-source">Source: MINAGRI / NISR</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline (2023/24)</th>
                                <th>Mid-Term (2026/27)</th>
                                <th>End Target (2028/29)</th>
                                <th>Data Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Maize yield (t/ha)</td>
                                <td>2.0</td>
                                <td>—</td>
                                <td>3.5</td>
                                <td>NISR (SAS)</td>
                            </tr>
                            <tr>
                                <td>Rice yield (t/ha)</td>
                                <td>4.0</td>
                                <td>4.8</td>
                                <td>5.4</td>
                                <td>NISR (SAS)</td>
                            </tr>
                            <tr>
                                <td>Irish Potato yield (t/ha)</td>
                                <td>8.2</td>
                                <td>11.4</td>
                                <td>14.7</td>
                                <td>NISR (SAS)</td>
                            </tr>
                            <tr>
                                <td>Wheat yield (t/ha)</td>
                                <td>1.2</td>
                                <td>1.38</td>
                                <td>1.48</td>
                                <td>NISR (SAS)</td>
                            </tr>
                            <tr>
                                <td>Soybean yield (t/ha)</td>
                                <td>0.4</td>
                                <td>1.1</td>
                                <td>1.4</td>
                                <td>NISR (SAS)</td>
                            </tr>
                            <tr>
                                <td>Cassava yield (t/ha)</td>
                                <td>13.5</td>
                                <td>16.5</td>
                                <td>17.9</td>
                                <td>NISR (SAS)</td>
                            </tr>
                            <tr>
                                <td>Cooking banana yield (t/ha)</td>
                                <td>14.2</td>
                                <td>17.9</td>
                                <td>19.7</td>
                                <td>NISR (SAS)</td>
                            </tr>
                            <tr>
                                <td>Fertilizer applied (kg/ha)</td>
                                <td>70</td>
                                <td>79.6</td>
                                <td>94.6</td>
                                <td>MINAGRI</td>
                            </tr>
                            <tr>
                                <td>Improved seeds — Large-scale farmers (%)</td>
                                <td>85.7</td>
                                <td>94</td>
                                <td>100</td>
                                <td>MINAGRI</td>
                            </tr>
                            <tr>
                                <td>Improved seeds — Small-scale farmers (%)</td>
                                <td>35.9</td>
                                <td>47</td>
                                <td>50</td>
                                <td>MINAGRI</td>
                            </tr>
                            <tr>
                                <td>Post-harvest loss reduction (%)</td>
                                <td>13.8</td>
                                <td>8.5</td>
                                <td>4.8</td>
                                <td>MINAGRI</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">1.2</span>
                    Irrigation & Land Management
                    <span class="sh-source">Source: MINAGRI</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline (2023/24)</th>
                                <th>Mid-Term (2026/27)</th>
                                <th>End Target (2028/29)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Area under irrigation (ha)</td>
                                <td>71,549</td>
                                <td>99,260</td>
                                <td>132,171</td>
                            </tr>
                            <tr>
                                <td>Area under radical terraces (ha)</td>
                                <td>142,318</td>
                                <td>155,768</td>
                                <td>167,268</td>
                            </tr>
                            <tr>
                                <td>Area under progressive terraces (ha)</td>
                                <td>1,032,282</td>
                                <td>1,071,282</td>
                                <td>1,102,282</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">1.3</span>
                    Livestock & Animal Resources
                    <span class="sh-source">Source: MINAGRI</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline (2023/24)</th>
                                <th>End Target (2028/29)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Milk production (MT)</td><td>1,061,301</td><td>1,323,561</td></tr>
                            <tr><td>Meat production (MT)</td><td>197,778</td><td>247,223</td></tr>
                            <tr><td>Fish production (MT)</td><td>46,495</td><td>77,700</td></tr>
                            <tr><td>Eggs production (MT)</td><td>17,344</td><td>20,813</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">1.4</span>
                    Export-Led Growth & Trade
                    <span class="sh-source">Source: MINECOFIN / NAEB</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline (2023/24)</th>
                                <th>End Target (2028/29)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Total exports (USD B)</td><td>3.5</td><td>7.3</td></tr>
                            <tr><td>Agri exports (USD M)</td><td>857.2</td><td>1,540</td></tr>
                            <tr><td>Export-to-import coverage ratio (%)</td><td>61</td><td>77</td></tr>
                            <tr><td>Coffee export volume (MT)</td><td>20,509</td><td>32,000</td></tr>
                            <tr><td>Credit to agriculture (% of total loans)</td><td>6</td><td>10</td></tr>
                            <tr><td>Private investment in agriculture (USD B)</td><td>2.2</td><td>4.6</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">1.5</span>
                    Jobs & Value Addition
                    <span class="sh-source">Source: MINECOFIN</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Annual agriculture sector growth</td><td>> 6%</td></tr>
                            <tr><td>Industrial sector growth</td><td>> 10%</td></tr>
                            <tr><td>Manufacturing growth</td><td>10.4%</td></tr>
                            <tr><td>Jobs created (annual, NST2 target)</td><td>250,000+</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ============ PILLAR 2: SOCIAL TRANSFORMATION ============ -->
        <section class="glass-card theory-pillar" style="--pillar-color: #0EA5E9;">
            <div class="pillar-header">
                <span class="pillar-icon-lg" style="background: linear-gradient(135deg, #0EA5E9, #6366F1);">👥</span>
                <div>
                    <h2>Pillar 2 — Social Transformation</h2>
                    <p>Digital inclusion, nutrition, gender, youth, and social protection</p>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">2.1</span>
                    Digital Inclusion & Literacy
                    <span class="sh-source">Source: NISR / MINECOFIN</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline</th>
                                <th>Target 2029</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>ICT literacy rate (%)</td><td>—</td><td>60%</td></tr>
                            <tr><td>Internet penetration (%)</td><td>30</td><td>95</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">2.2</span>
                    Nutrition & Food Security
                    <span class="sh-source">Source: NISR / MINAGRI</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline</th>
                                <th>Target 2029</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Stunting rate (under 5) (%)</td><td>33</td><td>15</td></tr>
                            <tr><td>Food insecurity (%)</td><td>20.4 (2021)</td><td>Reduced</td></tr>
                            <tr><td>Food self-sufficiency ratio (%)</td><td>79.6 (2024)</td><td>100</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">2.3</span>
                    Education & Health
                    <span class="sh-source">Source: MINECOFIN / UNDP</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline</th>
                                <th>Target 2029</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Pre-primary enrolment (%)</td><td>35</td><td>65</td></tr>
                            <tr><td>Life expectancy (years)</td><td>69.6</td><td>Improved</td></tr>
                            <tr><td>Under-5 mortality (per 1,000)</td><td>45</td><td>20</td></tr>
                            <tr><td>Universal health coverage</td><td>Expanding</td><td>Universal</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">2.4</span>
                    Gender & Social Protection
                    <span class="sh-source">Source: NISR / MINECOFIN</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Gender parity in leadership</td><td>50%</td></tr>
                            <tr><td>Social protection coverage</td><td>80%</td></tr>
                            <tr><td>Youth employment</td><td>12% unemployment target</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ============ PILLAR 3: TRANSFORMATIONAL GOVERNANCE ============ -->
        <section class="glass-card theory-pillar" style="--pillar-color: #F59E0B;">
            <div class="pillar-header">
                <span class="pillar-icon-lg" style="background: linear-gradient(135deg, #F59E0B, #EA580C);">🏛️</span>
                <div>
                    <h2>Pillar 3 — Transformational Governance</h2>
                    <p>Evidence-based policy, e-services, transparency, and decentralisation</p>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">3.1</span>
                    Evidence-Based Planning & E-Services
                    <span class="sh-source">Source: MINECOFIN / NISR</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline (2023/24)</th>
                                <th>End Target (2028/29)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Evidence-based planning (%)</td><td>40</td><td>90</td></tr>
                            <tr><td>E-government service usage (%)</td><td>30</td><td>75</td></tr>
                            <tr><td>Budget transparency index</td><td>55</td><td>85</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">3.2</span>
                    Decentralisation & Justice
                    <span class="sh-source">Source: MINALOC / MINIJUST</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline</th>
                                <th>Target 2029</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Judicial backlog reduction (%)</td><td>62</td><td>30</td></tr>
                            <tr><td>Decentralisation score</td><td>50</td><td>80</td></tr>
                            <tr><td>Anti-corruption effectiveness</td><td>60</td><td>90</td></tr>
                            <tr><td>Governance indicators tracked</td><td>—</td><td>50+</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ============ VISION 2050 ============ -->
        <section class="glass-card theory-pillar" style="--pillar-color: #7C3AED;">
            <div class="pillar-header">
                <span class="pillar-icon-lg" style="background: linear-gradient(135deg, #7C3AED, #C084FC);">🎯</span>
                <div>
                    <h2>Vision 2050 — Long-Term Aspirations</h2>
                    <p>High-income status, prosperity, longevity, and resilience</p>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">V.1</span>
                    Economic Targets
                    <span class="sh-source">Source: MINECOFIN / World Bank</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline</th>
                                <th>2035 Target</th>
                                <th>2050 Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>GDP per capita (USD)</td>
                                <td>~1,040</td>
                                <td>4,036 (UMI)</td>
                                <td>12,476 (HIC)</td>
                            </tr>
                            <tr>
                                <td>Average annual GDP growth (%)</td>
                                <td>—</td>
                                <td>12% (2018–2035)</td>
                                <td>10% (2036–2050)</td>
                            </tr>
                            <tr>
                                <td>Total exports (USD B)</td>
                                <td>2.5</td>
                                <td>—</td>
                                <td>—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="strategy-block">
                <h3 class="strategy-heading">
                    <span class="sh-num">V.2</span>
                    Social & Environmental Targets
                    <span class="sh-source">Source: MINECOFIN / NISR</span>
                </h3>
                <div class="theory-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Baseline</th>
                                <th>Target 2050</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Life expectancy (years)</td><td>67</td><td>80+</td></tr>
                            <tr><td>Internet penetration (%)</td><td>30</td><td>95+</td></tr>
                            <tr><td>Climate resilience score</td><td>45</td><td>90</td></tr>
                            <tr><td>Food self-sufficiency (%)</td><td>70</td><td>100</td></tr>
                            <tr><td>Urbanisation rate (%)</td><td>18</td><td>35</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ============ DATA SOURCES ============ -->
        <section class="glass-card sources-section">
            <div class="card-header"><h3>📚 Official Data Sources</h3></div>
            <div class="sources-grid">
                <div class="source-card">
                    <span class="source-icon">🏛️</span>
                    <strong>MINECOFIN</strong>
                    <small>Ministry of Finance & Economic Planning</small>
                    <p>NST2 macroeconomic framework, GDP targets, investment and export projections.</p>
                </div>
                <div class="source-card">
                    <span class="source-icon">📊</span>
                    <strong>NISR</strong>
                    <small>National Institute of Statistics of Rwanda</small>
                    <p>Statistical Yearbook, agricultural surveys, indicator metadata and reporting.</p>
                </div>
                <div class="source-card">
                    <span class="source-icon">🌾</span>
                    <strong>MINAGRI</strong>
                    <small>Ministry of Agriculture & Animal Resources</small>
                    <p>PSTA5, crop yield targets, irrigation, livestock, and post-harvest data.</p>
                </div>
                <div class="source-card">
                    <span class="source-icon">🌍</span>
                    <strong>World Bank / UNDP</strong>
                    <small>Development partners</small>
                    <p>Vision 2050 GDP per capita thresholds, SDG acceleration analysis.</p>
                </div>
            </div>
        </section>
    </main>
</div>

<style>
/* ========== THEORY PAGE ========== */
.theory-hero {
    padding: 30px !important; max-width: none;
    background: linear-gradient(135deg, rgba(22,163,74,0.15), rgba(124,58,237,0.08));
    display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;
}
.theory-hero h1 { margin: 6px 0 6px; font-size: 26px; }
.theory-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }
.back-link { color: var(--accent); text-decoration: none; font-size: 12px; display: inline-block; }
.back-link:hover { text-decoration: underline; }
.hero-meta { display: flex; gap: 10px; }

.theory-intro { padding: 30px !important; max-width: none; }
.intro-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start; }
.intro-text h2 { margin: 0 0 16px; font-size: 22px; color: var(--accent); }
.intro-text p { color: var(--text-sub); font-size: 14px; line-height: 1.7; margin: 0 0 12px; }
.intro-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.is-card {
    padding: 16px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 14px;
    text-align: center;
}
.is-card strong { display: block; font-size: 22px; font-weight: 800; color: var(--accent); }
.is-card small { display: block; font-size: 11px; color: var(--text-sub); margin-top: 4px; line-height: 1.4; }

.theory-pillar { padding: 30px !important; max-width: none; }
.pillar-header {
    display: flex; gap: 20px; align-items: center;
    padding-bottom: 20px; margin-bottom: 24px;
    border-bottom: 2px solid var(--pillar-color);
}
.pillar-icon-lg {
    width: 60px; height: 60px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; flex-shrink: 0;
}
.pillar-header h2 { margin: 0 0 4px; font-size: 22px; color: var(--pillar-color); }
.pillar-header p { margin: 0; font-size: 13px; color: var(--text-sub); }

.strategy-block { margin-bottom: 28px; }
.strategy-heading {
    display: flex; align-items: center; gap: 12px;
    font-size: 15px; font-weight: 700; margin: 0 0 14px; flex-wrap: wrap;
}
.sh-num {
    background: var(--pillar-color);
    color: white; padding: 2px 10px;
    border-radius: 6px; font-size: 11px; letter-spacing: 0.5px;
}
.sh-source {
    margin-left: auto; font-size: 10px;
    color: var(--text-sub); font-weight: 500;
    background: rgba(255,255,255,0.05);
    padding: 3px 10px; border-radius: 20px;
}

.theory-table-wrap { overflow-x: auto; }
.theory-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.theory-table th {
    text-align: left; padding: 10px 14px;
    background: rgba(255,255,255,0.04);
    color: var(--text-sub); font-size: 11px;
    text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border);
}
.theory-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    color: var(--text-main);
}
.theory-table tr:hover td { background: rgba(255,255,255,0.02); }
.theory-table td:first-child { color: var(--text-sub); }

.sources-section { padding: 30px !important; max-width: none; }
.sources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px; margin-top: 16px;
}
.source-card {
    padding: 20px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 14px;
    text-align: center;
    transition: 250ms;
}
.source-card:hover { transform: translateY(-3px); border-color: var(--accent); }
.source-icon { font-size: 28px; display: block; margin-bottom: 8px; }
.source-card strong { display: block; font-size: 14px; margin-bottom: 2px; }
.source-card small { display: block; font-size: 11px; color: var(--text-sub); margin-bottom: 8px; }
.source-card p { font-size: 12px; color: var(--text-sub); margin: 0; line-height: 1.5; }

@media (max-width: 900px) {
    .intro-grid { grid-template-columns: 1fr; }
    .theory-hero { flex-direction: column; align-items: flex-start; }
    .pillar-header { flex-direction: column; align-items: flex-start; }
    .sh-source { margin-left: 0; }
}
</style>