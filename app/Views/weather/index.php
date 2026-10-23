<?php
$wx       = $data['weather']  ?? null;
$advice   = $data['advice']   ?? [];
$district = $data['district'] ?? 'Huye';
$allDistricts = $data['all_districts'] ?? [];
$source   = $wx['source'] ?? 'fallback';
$isLive   = !empty($wx['ok']);
$uv       = $wx['uv'] ?? ['index' => 0, 'level' => 'Low', 'class' => 'ok'];

/* Safe field access — controller returns flat array */
$temp      = $wx['temp']       ?? 24;
$feelsLike = $wx['feels_like'] ?? 25;
$tempMin   = $wx['temp_min']   ?? 18;
$tempMax   = $wx['temp_max']   ?? 27;
$humidity  = $wx['humidity']   ?? 0;
$wind      = $wx['wind_speed'] ?? 0;
$windDir   = $wx['wind_dir']   ?? '—';
$rain      = $wx['rain_1h']    ?? 0;
$clouds    = $wx['clouds']     ?? 0;
$pressure  = $wx['pressure']   ?? 1013;
$condition = $wx['condition']  ?? 'Cloudy';
$desc      = $wx['description']?? 'Partly cloudy';
$icon      = $wx['icon']       ?? '02d';
$sunrise   = $wx['sunrise']    ?? '—';
$sunset    = $wx['sunset']     ?? '—';
$updatedAt = $wx['updated_at'] ?? '—';
$fc        = $wx['forecast']   ?? [];

/* Icon code → emoji */
function wxIcon($code) {
    $map = [
        '01d'=>'☀️','01n'=>'🌙','02d'=>'⛅','02n'=>'☁️',
        '03d'=>'☁️','03n'=>'☁️','04d'=>'☁️','04n'=>'☁️',
        '09d'=>'🌧️','09n'=>'🌧️','10d'=>'🌦️','10n'=>'🌧️',
        '11d'=>'⛈️','11n'=>'⛈️','13d'=>'❄️','13n'=>'❄️',
        '50d'=>'🌫️','50n'=>'🌫️',
    ];
    return $map[$code] ?? '🌤️';
}

/* Source label */
$sourceLabel = '🌐 Live';
$sourceClass = 'blue';
if ($source === 'fallback' || !$isLive) {
    $sourceLabel = '🔧 Fallback';
    $sourceClass = 'amber';
} elseif ($source === 'cache') {
    $sourceLabel = '⚡ Cached';
}
?>
<div class="dashboard-layout weather-page">
    <?php
    $sidebar = __DIR__ . '/../layouts/sidebar.php';
    if (is_file($sidebar)) require_once $sidebar;
    ?>

    <div class="main-wrap">
        <?php
        $topbar = __DIR__ . '/../layouts/topbar.php';
        if (is_file($topbar)) require_once $topbar;
        ?>

        <main class="main-content">

            <!-- ═══════════ LOCATION BAR ═══════════ -->
            <section class="glass-card location-bar">
                <div class="location-left">
                    <span class="loc-icon">📍</span>
                    <div>
                        <span class="loc-label">Weather for</span>
                        <strong class="loc-name" id="locName"><?= e($district) ?> District</strong>
                    </div>
                </div>

                <div class="location-right">
                    <select id="districtPicker" class="district-select">
                        <?php if (empty($allDistricts)): ?>
                            <option value="<?= e($district) ?>"><?= e($district) ?></option>
                        <?php else: ?>
                            <?php foreach ($allDistricts as $d): ?>
                                <option value="<?= e($d) ?>" <?= $d === $district ? 'selected' : '' ?>>
                                    <?= e($d) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <button type="button" class="btn-loc" id="btnGeo">
                        🎯 Use My Location
                    </button>
                </div>
            </section>

            <!-- ═══════════ DASHBOARD GRID ═══════════ -->
            <div class="weather-layout">

                <!-- LEFT COLUMN -->
                <div class="weather-main">

                    <!-- CURRENT CONDITIONS -->
                    <section class="glass-card current-card">
                        <div class="current-main">
                            <div class="current-icon" id="curIcon">
                                <?= wxIcon($icon) ?>
                            </div>
                            <div class="current-temp">
                                <span class="temp-value" id="curTemp"><?= e($temp) ?>°C</span>
                                <span class="temp-desc" id="curDesc"><?= e($desc) ?></span>
                                <span class="temp-feels" id="curFeels">Feels like <?= e($feelsLike) ?>°C</span>
                                <span class="temp-updated">
                                    <span class="live-dot <?= $isLive ? 'ok' : 'off' ?>"></span>
                                    Updated <span id="curUpdated"><?= e($updatedAt) ?></span>
                                </span>
                            </div>
                        </div>

                        <div class="current-stats">
                            <div class="stat-item humidity">
                                <span class="stat-label">💧 Humidity</span>
                                <strong class="stat-value" id="curHumidity"><?= e($humidity) ?>%</strong>
                            </div>
                            <div class="stat-item wind">
                                <span class="stat-label">💨 Wind</span>
                                <strong class="stat-value" id="curWind"><?= e($wind) ?> km/h <small id="curWindDir"><?= e($windDir) ?></small></strong>
                            </div>
                            <div class="stat-item rain">
                                <span class="stat-label">🌧️ Rain (1h)</span>
                                <strong class="stat-value" id="curRain"><?= e($rain) ?> mm</strong>
                            </div>
                            <div class="stat-item clouds">
                                <span class="stat-label">☁️ Clouds</span>
                                <strong class="stat-value" id="curClouds"><?= e($clouds) ?>%</strong>
                            </div>
                            <div class="stat-item pressure">
                                <span class="stat-label">🌡️ Pressure</span>
                                <strong class="stat-value" id="curPressure"><?= e($pressure) ?> hPa</strong>
                            </div>
                            <div class="stat-item uv">
                                <span class="stat-label">☀️ UV Index</span>
                                <strong class="stat-value uv-<?= e($uv['class']) ?>" id="curUv">
                                    <?= (int)$uv['index'] ?> · <?= e($uv['level']) ?>
                                </strong>
                            </div>
                        </div>

                        <div class="sun-row">
                            <div class="sun-item">
                                <span>🌅</span>
                                <div>
                                    <small>Sunrise</small>
                                    <strong id="curSunrise"><?= e($sunrise) ?></strong>
                                </div>
                            </div>
                            <div class="sun-item">
                                <span>🌇</span>
                                <div>
                                    <small>Sunset</small>
                                    <strong id="curSunset"><?= e($sunset) ?></strong>
                                </div>
                            </div>
                            <div class="sun-item">
                                <span>📊</span>
                                <div>
                                    <small>Range</small>
                                    <strong>
                                        <span id="curTempMin"><?= e($tempMin) ?></span>° – <span id="curTempMax"><?= e($tempMax) ?></span>°
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 5-DAY FORECAST -->
                    <section class="glass-card forecast-card">
                        <div class="card-head">
                            <h3>📅 5-Day Forecast</h3>
                            <span class="badge <?= $sourceClass ?>" id="sourceBadge">
                                <?= $sourceLabel ?>
                            </span>
                        </div>

                        <div class="forecast-grid" id="forecastGrid">
                            <?php if (empty($fc)): ?>
                                <div class="forecast-empty">No forecast data — check API key</div>
                            <?php else: ?>
                                <?php foreach ($fc as $i => $d): ?>
                                    <div class="forecast-day">
                                        <span class="forecast-day-name"><?= $i === 0 ? 'Today' : e($d['day'] ?? '') ?></span>
                                        <span class="forecast-icon"><?= wxIcon($d['icon'] ?? '02d') ?></span>
                                        <div class="forecast-temps">
                                            <strong><?= (int)($d['temp_max'] ?? 25) ?>°</strong>
                                            <span><?= (int)($d['temp_min'] ?? 17) ?>°</span>
                                        </div>
                                        <div class="forecast-rain">
                                            💧 <?= (float)($d['rain'] ?? 0) ?>mm
                                        </div>
                                        <?php if (isset($d['pop']) && $d['pop'] > 0): ?>
                                            <div class="forecast-pop">☔ <?= (int)$d['pop'] ?>%</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                </div>

                <!-- RIGHT COLUMN — ADVICE -->
                <div class="weather-side">

                    <section class="glass-card advice-card planting">
                        <div class="advice-head">
                            <span class="advice-icon">🌱</span>
                            <h3>Planting Advice</h3>
                        </div>
                        <ul class="advice-list" id="advice-planting">
                            <?php foreach (($advice['planting'] ?? []) as $a): ?>
                                <li class="advice-item lvl-<?= e($a['level'] ?? 'ok') ?>">
                                    <span class="advice-emoji"><?= $a['icon'] ?? '•' ?></span>
                                    <span><?= e($a['text'] ?? '') ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($advice['planting'])): ?>
                                <li class="advice-item lvl-ok"><span class="advice-emoji">📊</span><span>Loading…</span></li>
                            <?php endif; ?>
                        </ul>
                    </section>

                    <section class="glass-card advice-card irrigation">
                        <div class="advice-head">
                            <span class="advice-icon">💧</span>
                            <h3>Irrigation</h3>
                        </div>
                        <ul class="advice-list" id="advice-irrigation">
                            <?php foreach (($advice['irrigation'] ?? []) as $a): ?>
                                <li class="advice-item lvl-<?= e($a['level'] ?? 'ok') ?>">
                                    <span class="advice-emoji"><?= $a['icon'] ?? '•' ?></span>
                                    <span><?= e($a['text'] ?? '') ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($advice['irrigation'])): ?>
                                <li class="advice-item lvl-ok"><span class="advice-emoji">📊</span><span>Loading…</span></li>
                            <?php endif; ?>
                        </ul>
                    </section>

                    <section class="glass-card advice-card protection">
                        <div class="advice-head">
                            <span class="advice-icon">🛡️</span>
                            <h3>Crop Protection</h3>
                        </div>
                        <ul class="advice-list" id="advice-protection">
                            <?php foreach (($advice['protection'] ?? []) as $a): ?>
                                <li class="advice-item lvl-<?= e($a['level'] ?? 'ok') ?>">
                                    <span class="advice-emoji"><?= $a['icon'] ?? '•' ?></span>
                                    <span><?= e($a['text'] ?? '') ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($advice['protection'])): ?>
                                <li class="advice-item lvl-ok"><span class="advice-emoji">📊</span><span>Loading…</span></li>
                            <?php endif; ?>
                        </ul>
                    </section>

                </div>

            </div>

        </main>
    </div>
</div>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';
    var picker = document.getElementById('districtPicker');
    var btnGeo = document.getElementById('btnGeo');

    var iconMap = {
        '01d':'☀️','01n':'🌙','02d':'⛅','02n':'☁️',
        '03d':'☁️','03n':'☁️','04d':'☁️','04n':'☁️',
        '09d':'🌧️','09n':'🌧️','10d':'🌦️','10n':'🌧️',
        '11d':'⛈️','11n':'⛈️','13d':'❄️','13n':'❄️',
        '50d':'🌫️','50n':'🌫️'
    };
    function ic(code) { return iconMap[code] || '🌤️'; }

    /* ═══ Loading overlay ═══ */
    var overlay = null;
    function showLoading() {
        if (overlay) return;
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);
    }
    function hideLoading() {
        if (overlay) { overlay.remove(); overlay = null; }
    }

    /* ═══ JSON fetch with HTML detection ═══ */
    async function fetchJson(url, options) {
        var res = await fetch(url, Object.assign({
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }, options || {}));
        var text = await res.text();
        try { return JSON.parse(text); }
        catch (e) {
            console.error('Non-JSON from ' + url + ':', text.substring(0, 400));
            throw new Error('Server returned HTML instead of JSON. Check /weather/current exists.');
        }
    }

    /* ═══ Load by district ═══ */
    async function loadByDistrict(district) {
        showLoading();
        try {
            var data = await fetchJson(BASE + '/weather/current?district=' + encodeURIComponent(district));
            if (!data.success) throw new Error(data.error || 'Failed');
            applyWeather(data);
            toast('✅ Weather updated: ' + data.district, 'success');
        } catch (err) {
            toast('⚠️ ' + err.message, 'error');
        } finally {
            hideLoading();
        }
    }

    /* ═══ Load by coords ═══ */
    async function loadByCoords(lat, lng, autoSave) {
        showLoading();
        try {
            var data = await fetchJson(BASE + '/weather/byCoords?lat=' + lat + '&lng=' + lng);
            if (!data.success) throw new Error(data.error || 'Failed');

            applyWeather(data);
            updatePicker(data.district);

            if (autoSave) {
                var fd = new URLSearchParams();
                fd.append('lat', lat);
                fd.append('lng', lng);
                fd.append('district', data.district);

                var saved = await fetchJson(BASE + '/weather/saveLocation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: fd.toString()
                });

                if (saved.success) {
                    if (typeof updateLocationChip === 'function') updateLocationChip(saved.district);
                    toast('📍 Location set: ' + saved.district, 'success');
                }
            } else {
                toast('📍 Located: ' + data.district, 'success');
            }
        } catch (err) {
            toast('⚠️ ' + err.message, 'error');
        } finally {
            hideLoading();
        }
    }

    /* ═══ Apply weather to DOM — new flat format ═══ */
    function applyWeather(data) {
        var wx = data.weather;
        if (!wx) return;

        /* Support BOTH shapes: flat (new controller) and nested (old) */
        var cur = wx.current || wx;
        var fc  = wx.forecast || [];

        setText('locName', (data.district || wx.district || '') + ' District');
        setText('curIcon', ic(cur.icon || wx.icon));
        setText('curTemp', (cur.temp ?? wx.temp) + '°C');
        setText('curDesc', cur.description || wx.description || '—');
        setText('curFeels', 'Feels like ' + (cur.feels_like ?? wx.feels_like) + '°C');
        setText('curUpdated', wx.updated_at || new Date().toLocaleTimeString());

        setText('curHumidity', (cur.humidity ?? wx.humidity) + '%');
        setText('curWind', (cur.wind_speed ?? wx.wind_speed) + ' km/h ');
        setText('curWindDir', cur.wind_dir || wx.wind_dir || '—');
        setText('curRain', (cur.rain_1h ?? wx.rain_1h) + ' mm');
        setText('curClouds', (cur.clouds ?? wx.clouds) + '%');
        setText('curPressure', (cur.pressure ?? wx.pressure ?? 1013) + ' hPa');
        setText('curSunrise', wx.sunrise || cur.sunrise || '—');
        setText('curSunset',  wx.sunset  || cur.sunset  || '—');
        setText('curTempMin', cur.temp_min ?? wx.temp_min);
        setText('curTempMax', cur.temp_max ?? wx.temp_max);

        /* UV */
        var uv = wx.uv || null;
        var uvEl = document.getElementById('curUv');
        if (uv && uvEl) {
            uvEl.textContent = uv.index + ' · ' + uv.level;
            uvEl.className = 'stat-value uv-' + (uv.class || 'ok');
        }

        /* Forecast grid */
        var grid = document.getElementById('forecastGrid');
        if (grid && fc.length) {
            grid.innerHTML = fc.map(function (d, i) {
                return '<div class="forecast-day">' +
                    '<span class="forecast-day-name">' + (i === 0 ? 'Today' : (d.day || '')) + '</span>' +
                    '<span class="forecast-icon">' + ic(d.icon) + '</span>' +
                    '<div class="forecast-temps"><strong>' + (d.temp_max ?? '—') + '°</strong><span>' + (d.temp_min ?? '—') + '°</span></div>' +
                    '<div class="forecast-rain">💧 ' + (d.rain ?? 0) + 'mm</div>' +
                    (d.pop ? '<div class="forecast-pop">☔ ' + d.pop + '%</div>' : '') +
                '</div>';
            }).join('');
        }

        /* Advice */
        if (data.advice) {
            renderAdvice('advice-planting',   data.advice.planting);
            renderAdvice('advice-irrigation', data.advice.irrigation);
            renderAdvice('advice-protection', data.advice.protection);
        }

        /* Source badge */
        var badge = document.getElementById('sourceBadge');
        if (badge && wx.source) {
            if (wx.source === 'live')         { badge.textContent = '🌐 Live';      badge.className = 'badge blue'; }
            else if (wx.source === 'cache')   { badge.textContent = '⚡ Cached';    badge.className = 'badge blue'; }
            else if (wx.source === 'fallback'){ badge.textContent = '🔧 Fallback';  badge.className = 'badge amber'; }
        }
    }

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function renderAdvice(containerId, items) {
        var el = document.getElementById(containerId);
        if (!el || !items) return;
        el.innerHTML = items.map(function (a) {
            return '<li class="advice-item lvl-' + (a.level || 'ok') + '">' +
                   '<span class="advice-emoji">' + (a.icon || '•') + '</span>' +
                   '<span>' + escapeHtml(a.text || '') + '</span>' +
                   '</li>';
        }).join('');
    }

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function updatePicker(district) {
        if (!picker) return;
        for (var i = 0; i < picker.options.length; i++) {
            if (picker.options[i].value === district) {
                picker.selectedIndex = i;
                return;
            }
        }
        picker.add(new Option(district, district, true, true));
    }

    /* ═══ Events ═══ */
    if (picker) {
        picker.addEventListener('change', function () {
            var district = this.value;

            var fd = new URLSearchParams();
            fd.append('district', district);

            fetch(BASE + '/weather/setDistrict', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: fd.toString(),
                credentials: 'same-origin'
            }).then(function () {
                if (typeof updateLocationChip === 'function') updateLocationChip(district);
            });

            loadByDistrict(district);
        });
    }

   if (btnGeo) {
    btnGeo.addEventListener('click', async function () {
        btnGeo.disabled = true;
        var original = btnGeo.innerHTML;
        btnGeo.innerHTML = '⏳ Locating…';

        try {
            /* ── Attempt 1: Native Geolocation API (needs HTTPS) ── */
            if (navigator.geolocation && window.isSecureContext) {
                try {
                    var pos = await new Promise(function (resolve, reject) {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: false,
                            timeout: 8000,
                            maximumAge: 300000
                        });
                    });
                    await loadByCoords(pos.coords.latitude, pos.coords.longitude, true);
                    toast('📍 Location updated via GPS', 'success');
                    return;
                } catch (gpsErr) {
                    console.warn('GPS failed:', gpsErr.message || gpsErr.code);
                    /* Fall through to IP detection */
                }
            } else if (navigator.geolocation && !window.isSecureContext) {
                toast('🔒 GPS needs HTTPS — trying IP detection…', 'info');
            }

            /* ── Attempt 2: IP-based geolocation (works on HTTP) ── */
            var ipData = await fetchJson(BASE + '/weather/autoDetect');
            if (ipData.success && ipData.district) {
                updatePicker(ipData.district);
                await loadByDistrict(ipData.district);

                /* Persist to session */
                var fd = new URLSearchParams();
                fd.append('district', ipData.district);
                fd.append('lat', ipData.lat);
                fd.append('lng', ipData.lng);
                fetch(BASE + '/weather/saveLocation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: fd.toString(),
                    credentials: 'same-origin'
                }).catch(function () {});

                var msg = ipData.outside
                    ? '🌍 Outside Rwanda — using ' + ipData.district
                    : '📍 Detected: ' + ipData.district + ' (via IP)';
                toast(msg, 'success');
                return;
            }

            /* ── Attempt 3: Prompt user to pick a district ── */
            toast('Could not auto-detect. Please pick a district manually.', 'info');
            if (picker) {
                picker.focus();
                picker.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

        } catch (err) {
            console.error(err);
            toast('⚠️ ' + (err.message || 'Location failed'), 'error');
            if (picker) picker.focus();
        } finally {
            btnGeo.disabled = false;
            btnGeo.innerHTML = original;
        }
    });
}

    /* ═══ Auto-refresh every 10 minutes ═══ */
    setInterval(function () {
        if (document.hidden || !picker) return;
        fetchJson(BASE + '/weather/current?district=' + encodeURIComponent(picker.value))
            .then(function (d) { if (d.success) applyWeather(d); })
            .catch(function () {});
    }, 10 * 60 * 1000);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden || !picker) return;
        fetchJson(BASE + '/weather/current?district=' + encodeURIComponent(picker.value))
            .then(function (d) { if (d.success) applyWeather(d); })
            .catch(function () {});
    });

    /* ═══ Toast ═══ */
    function toast(msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2600);
    }
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════════
   BASE — force full width
   ═══════════════════════════════════════════════════════════════ */
html, body {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    overflow-x: hidden !important;
}

.weather-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

.weather-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    box-sizing: border-box !important;
}

.weather-page .main-content {
    padding: 24px 32px 60px !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
}

.weather-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 24px !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    width: 100% !important;
}

/* ═══════════ LOCATION BAR ═══════════ */
.location-bar {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 20px !important;
    flex-wrap: wrap !important;
    padding: 18px 24px !important;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(16, 185, 129, 0.06)) !important;
}

.location-left { display: flex !important; align-items: center !important; gap: 14px !important; min-width: 200px !important; }
.loc-icon { font-size: 28px !important; }
.loc-label {
    display: block !important; font-size: 11px !important; font-weight: 700 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.8px !important; margin-bottom: 2px !important;
}
.loc-name { display: block !important; font-size: 16px !important; color: #f1f5f9 !important; font-weight: 800 !important; }

.location-right { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; align-items: center !important; }

.district-select {
    padding: 12px 42px 12px 16px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 13.5px !important; font-weight: 600 !important;
    font-family: inherit !important; cursor: pointer !important;
    outline: none !important;
    -webkit-appearance: none !important; appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 14px center !important;
    background-size: 15px !important;
    min-width: 180px !important;
}
.district-select option { background: #1e293b !important; color: #f1f5f9 !important; }

.btn-loc {
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    padding: 12px 22px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important; border: none !important;
    border-radius: 10px !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s !important; white-space: nowrap !important;
}
.btn-loc:hover:not(:disabled) { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important; }
.btn-loc:disabled { opacity: 0.6 !important; cursor: wait !important; }

/* ═══════════ GRID ═══════════ */
.weather-layout {
    display: grid !important;
    grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr) !important;
    gap: 18px !important;
    align-items: start !important;
    width: 100% !important;
}
.weather-main { display: flex !important; flex-direction: column !important; gap: 18px !important; min-width: 0 !important; }
.weather-side { display: flex !important; flex-direction: column !important; gap: 18px !important; min-width: 0 !important; }

/* ═══════════ CURRENT CARD ═══════════ */
.current-card {
    display: flex !important; flex-direction: column !important;
    gap: 22px !important;
    padding: 30px !important;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(16, 185, 129, 0.06)) !important;
}

.current-main {
    display: flex !important; align-items: center !important;
    gap: 28px !important; flex-wrap: wrap !important;
}

.current-icon {
    font-size: 92px !important; line-height: 1 !important;
    filter: drop-shadow(0 8px 24px rgba(56, 189, 248, 0.3)) !important;
}

.current-temp { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.temp-value {
    font-size: 60px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; line-height: 1 !important; letter-spacing: -2px !important;
}
.temp-desc  { font-size: 16px !important; color: #cbd5e1 !important; font-weight: 600 !important; }
.temp-feels { font-size: 13px !important; color: #94a3b8 !important; }
.temp-updated {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    font-size: 11.5px !important; color: #64748b !important;
    margin-top: 4px !important;
}
.live-dot {
    display: inline-block !important;
    width: 6px !important; height: 6px !important; border-radius: 50% !important;
}
.live-dot.ok  { background: #10b981 !important; box-shadow: 0 0 8px #10b981 !important; animation: pulseLive 1.6s infinite !important; }
.live-dot.off { background: #fbbf24 !important; box-shadow: 0 0 8px #fbbf24 !important; }
@keyframes pulseLive { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

.current-stats {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 12px !important;
    width: 100% !important;
}

.stat-item {
    display: flex !important; flex-direction: column !important; gap: 4px !important;
    padding: 14px 16px !important;
    background: rgba(0, 0, 0, 0.25) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 12px !important;
}
.stat-label { font-size: 11.5px !important; color: #94a3b8 !important; font-weight: 600 !important; }
.stat-value { font-size: 18px !important; font-weight: 800 !important; color: #34d399 !important; }
.stat-value small { font-size: 11px !important; color: #64748b !important; font-weight: 600 !important; margin-left: 4px !important; }

.uv-danger { color: #f87171 !important; }
.uv-warn   { color: #fbbf24 !important; }
.uv-ok     { color: #34d399 !important; }

/* Sun row */
.sun-row {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 12px !important;
    padding-top: 18px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
}
.sun-item { display: flex !important; align-items: center !important; gap: 10px !important; }
.sun-item > span { font-size: 22px !important; }
.sun-item small {
    display: block !important; font-size: 10.5px !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.5px !important; font-weight: 700 !important; margin-bottom: 2px !important;
}
.sun-item strong { font-size: 14px !important; color: #f1f5f9 !important; font-weight: 800 !important; }

/* ═══════════ FORECAST ═══════════ */
.card-head {
    display: flex !important; justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 18px !important; padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important; gap: 12px !important;
}
.card-head h3 { font-size: 16px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }

.badge {
    display: inline-flex !important;
    padding: 5px 12px !important; border-radius: 999px !important;
    font-size: 10.5px !important; font-weight: 700 !important;
    text-transform: uppercase !important; letter-spacing: 0.5px !important;
}
.badge.blue  { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; }
.badge.amber { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(251, 191, 36, 0.3) !important; }

.forecast-grid {
    display: grid !important;
    grid-template-columns: repeat(5, 1fr) !important;
    gap: 10px !important;
}

.forecast-day {
    display: flex !important; flex-direction: column !important;
    align-items: center !important; gap: 8px !important;
    padding: 16px 10px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 14px !important;
    transition: all 0.2s !important;
}
.forecast-day:hover {
    background: rgba(56, 189, 248, 0.08) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
    transform: translateY(-2px) !important;
}

.forecast-day-name {
    font-size: 11px !important; font-weight: 700 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}
.forecast-icon { font-size: 30px !important; line-height: 1 !important; }
.forecast-temps { display: flex !important; gap: 8px !important; align-items: baseline !important; }
.forecast-temps strong { font-size: 15px !important; font-weight: 800 !important; color: #f1f5f9 !important; }
.forecast-temps span   { font-size: 12px !important; color: #94a3b8 !important; }
.forecast-rain { font-size: 11px !important; color: #38bdf8 !important; font-weight: 600 !important; }
.forecast-pop  { font-size: 10px !important; color: #a78bfa !important; font-weight: 700 !important; }
.forecast-empty {
    grid-column: 1 / -1 !important;
    padding: 30px !important; text-align: center !important;
    color: #94a3b8 !important; font-size: 13px !important;
}

/* ═══════════ ADVICE ═══════════ */
.advice-card { display: flex !important; flex-direction: column !important; gap: 14px !important; padding: 22px !important; }
.advice-head {
    display: flex !important; align-items: center !important; gap: 10px !important;
    padding-bottom: 12px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}
.advice-icon { font-size: 22px !important; }
.advice-head h3 { font-size: 15px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }

.advice-list {
    list-style: none !important; padding: 0 !important; margin: 0 !important;
    display: flex !important; flex-direction: column !important; gap: 10px !important;
}
.advice-item {
    display: flex !important; align-items: flex-start !important; gap: 10px !important;
    padding: 12px 14px !important;
    border-radius: 10px !important;
    font-size: 13px !important; line-height: 1.5 !important;
    color: #cbd5e1 !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border-left: 3px solid #38bdf8 !important;
}
.advice-item.lvl-good { border-left-color: #10b981 !important; background: rgba(16, 185, 129, 0.06) !important; }
.advice-item.lvl-ok   { border-left-color: #fbbf24 !important; background: rgba(251, 191, 36, 0.06) !important; }
.advice-item.lvl-warn { border-left-color: #f87171 !important; background: rgba(248, 113, 113, 0.06) !important; }
.advice-emoji { font-size: 16px !important; flex-shrink: 0 !important; line-height: 1.4 !important; }

/* ═══════════ LOADING ═══════════ */
.loading-overlay {
    position: fixed !important; inset: 0 !important;
    background: rgba(11, 17, 32, 0.7) !important;
    backdrop-filter: blur(4px) !important;
    -webkit-backdrop-filter: blur(4px) !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    z-index: 99998 !important;
}
.loading-spinner {
    width: 48px !important; height: 48px !important;
    border: 4px solid rgba(56, 189, 248, 0.2) !important;
    border-top-color: #38bdf8 !important;
    border-radius: 50% !important;
    animation: spin 0.8s linear infinite !important;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ═══════════ TOAST ═══════════ */
.toast {
    position: fixed !important;
    bottom: 28px !important; right: 28px !important;
    padding: 14px 22px !important;
    border-radius: 12px !important;
    font-size: 13.5px !important; font-weight: 600 !important;
    color: #ffffff !important;
    transform: translateY(20px) !important; opacity: 0 !important;
    transition: all 0.3s ease !important;
    z-index: 999999 !important;
    max-width: 400px !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
}
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
.toast.info    { background: linear-gradient(135deg, #38bdf8, #0284c7) !important; }

/* ═══════════ RESPONSIVE ═══════════ */
@media (max-width: 1100px) {
    .weather-layout { grid-template-columns: 1fr !important; }
}
@media (max-width: 900px) {
    .current-stats { grid-template-columns: repeat(2, 1fr) !important; }
    .forecast-grid { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 768px) {
    .weather-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 64px !important;
    }
    .weather-page .main-content { padding: 16px !important; }
    .weather-page .glass-card { padding: 18px !important; border-radius: 16px !important; }
    .location-bar { flex-direction: column !important; align-items: flex-start !important; }
    .location-right { width: 100% !important; flex-direction: column !important; }
    .district-select, .btn-loc { width: 100% !important; }
    .current-main { gap: 18px !important; }
    .current-icon { font-size: 64px !important; }
    .temp-value { font-size: 44px !important; }
    .current-stats { grid-template-columns: repeat(2, 1fr) !important; }
    .sun-row { grid-template-columns: 1fr !important; }
    .forecast-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; }
}
    /* ═══════════════════════════════════════════════════════════
   FIX: Right column overflow — advice text wraps instead of
   being cut off at the viewport edge.
   ═══════════════════════════════════════════════════════════ */

/* 1. Balance the two columns — right side gets a minimum width */
.weather-layout {
    grid-template-columns: minmax(0, 1.4fr) minmax(340px, 1fr) !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

/* 2. Force every grid/flex child to shrink instead of overflowing */
.weather-layout > *,
.weather-main,
.weather-side,
.advice-card,
.advice-list,
.advice-item {
    min-width: 0 !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

/* 3. Force advice text to wrap — this is the actual fix */
.advice-item {
    overflow: hidden !important;
    flex-wrap: nowrap !important;
}
.advice-item > span:last-child {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    word-break: break-word !important;
    overflow-wrap: anywhere !important;
    white-space: normal !important;
    display: block !important;
    line-height: 1.5 !important;
}
.advice-item .advice-emoji {
    flex: 0 0 auto !important;
}

/* 4. Prevent the main content from horizontal scrolling */
.weather-page .main-content {
    overflow-x: hidden !important;
}

/* 5. Responsive: stack when there's not enough room */
@media (max-width: 1250px) {
    .weather-layout {
        grid-template-columns: 1fr !important;
    }
}
</style>