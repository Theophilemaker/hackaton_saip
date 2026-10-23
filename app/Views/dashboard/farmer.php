<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- KPI Cards -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">🌾</div>
                <div>
                    <p class="kpi-label"><?= __('dashboard.kpi.production') ?></p>
                    <h3 class="kpi-value">4.8 <small>tons</small></h3>
                    <span class="kpi-trend up">▲ 12%</span>
                </div>
            </div>

            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#84CC16,#16A34A)">🏡</div>
                <div>
                    <p class="kpi-label"><?= __('dashboard.kpi.farms') ?></p>
                    <h3 class="kpi-value">3</h3>
                    <span class="kpi-trend up">▲ 1 new</span>
                </div>
            </div>

            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">💰</div>
                <div>
                    <p class="kpi-label"><?= __('dashboard.kpi.market') ?></p>
                    <h3 class="kpi-value">1.2M <small>RWF</small></h3>
                    <span class="kpi-trend up">▲ 8%</span>
                </div>
            </div>

            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">🌧️</div>
                <div>
                    <p class="kpi-label"><?= __('dashboard.kpi.rainfall') ?></p>
                    <h3 class="kpi-value">78<small>%</small></h3>
                    <span class="kpi-trend">Normal</span>
                </div>
            </div>
        </section>

        <!-- Main Grid -->
        <section class="dashboard-grid">
            <!-- Yield Chart -->
            <div class="glass-card chart-card">
                <div class="card-header">
                    <h3><?= __('dashboard.chart.yield') ?></h3>
                    <span class="badge">AI Powered</span>
                </div>
                <canvas id="yieldChart" height="120"></canvas>
            </div>

            <!-- Price Trends -->
            <div class="glass-card chart-card">
                <div class="card-header">
                    <h3><?= __('dashboard.chart.price') ?></h3>
                    <span class="badge">Weekly</span>
                </div>
                <canvas id="priceChart" height="120"></canvas>
            </div>

            <!-- Weather Widget -->
            <div class="glass-card weather-card">
                <div class="card-header">
                    <h3><?= __('dashboard.weather.title') ?></h3>
                </div>
                <div class="weather-main">
                    <div class="weather-temp">
                        <span class="w-icon">🌤️</span>
                        <div>
                            <h2>24°C</h2>
                            <p><?= __('dashboard.weather.condition') ?></p>
                        </div>
                    </div>
                    <div class="weather-details">
                        <div><small>Humidity</small><strong>65%</strong></div>
                        <div><small>Wind</small><strong>12 km/h</strong></div>
                        <div><small>Rain</small><strong>20%</strong></div>
                    </div>
                </div>
                <div class="forecast-row">
                    <div class="forecast-day"><p>Mon</p><span>🌧️</span><small>22°</small></div>
                    <div class="forecast-day"><p>Tue</p><span>⛅</span><small>24°</small></div>
                    <div class="forecast-day"><p>Wed</p><span>☀️</span><small>26°</small></div>
                    <div class="forecast-day"><p>Thu</p><span>⛅</span><small>23°</small></div>
                    <div class="forecast-day"><p>Fri</p><span>🌧️</span><small>21°</small></div>
                </div>
            </div>

            <!-- AI Recommendation -->
            <div class="glass-card ai-card">
                <div class="card-header">
                    <h3>🤖 <?= __('dashboard.ai.title') ?></h3>
                    <span class="badge green">Live</span>
                </div>
                <div class="ai-message">
                    <div class="ai-avatar">🌱</div>
                    <p><?= __('dashboard.ai.message') ?></p>
                </div>
                <div class="ai-actions">
                    <button class="btn-sm">Ask AgriBot</button>
                    <button class="btn-sm ghost">View Details</button>
                </div>
            </div>

            <!-- Market Prices -->
            <div class="glass-card market-card">
                <div class="card-header">
                    <h3><?= __('dashboard.market.title') ?></h3>
                    <span class="badge">Live</span>
                </div>
                <ul class="market-list">
                    <li>
                        <span>🌽 <?= __('dashboard.market.maize') ?></span>
                        <strong>350 RWF/kg</strong>
                        <span class="trend up">▲ 4%</span>
                    </li>
                    <li>
                        <span>🫘 <?= __('dashboard.market.beans') ?></span>
                        <strong>620 RWF/kg</strong>
                        <span class="trend down">▼ 2%</span>
                    </li>
                    <li>
                        <span>🥔 <?= __('dashboard.market.potatoes') ?></span>
                        <strong>280 RWF/kg</strong>
                        <span class="trend up">▲ 6%</span>
                    </li>
                </ul>
            </div>

            <!-- Notifications -->
            <div class="glass-card notif-card">
                <div class="card-header">
                    <h3>🔔 <?= __('dashboard.notifications.title') ?></h3>
                </div>
                <ul class="notif-list">
                    <li class="notif-item high">
                        <span class="dot"></span>
                        <p><?= __('dashboard.notifications.alert1') ?></p>
                    </li>
                    <li class="notif-item medium">
                        <span class="dot"></span>
                        <p><?= __('dashboard.notifications.alert2') ?></p>
                    </li>
                </ul>
            </div>
        </section>
    </main>
</div>