<div class="geo-cascade" id="geoCascade<?= $prefix ?? '' ?>">
    <div class="form-group">
        <label>Province</label>
        <select name="province_id" id="province<?= $prefix ?? '' ?>" onchange="loadDistricts<?= $prefix ?? '' ?>()">
            <option value="">Select Province</option>
        </select>
    </div>
    <div class="form-group">
        <label>District</label>
        <select name="district_id" id="district<?= $prefix ?? '' ?>" onchange="loadSectors<?= $prefix ?? '' ?>()">
            <option value="">Select District</option>
        </select>
    </div>
    <div class="form-group">
        <label>Sector</label>
        <select name="sector_id" id="sector<?= $prefix ?? '' ?>" onchange="loadCells<?= $prefix ?? '' ?>()">
            <option value="">Select Sector</option>
        </select>
    </div>
    <div class="form-group">
        <label>Cell</label>
        <select name="cell_id" id="cell<?= $prefix ?? '' ?>" onchange="loadVillages<?= $prefix ?? '' ?>()">
            <option value="">Select Cell</option>
        </select>
    </div>
    <div class="form-group">
        <label>Village</label>
        <select name="village_id" id="village<?= $prefix ?? '' ?>">
            <option value="">Select Village</option>
        </select>
    </div>
</div>

<script>
(function() {
    const P = '<?= $prefix ?? '' ?>';
    const base = '<?= BASE_URL ?>';

    fetch(base + '/geo/provinces').then(r => r.json()).then(data => {
        const sel = document.getElementById('province' + P);
        data.forEach(p => sel.innerHTML += `<option value="${p.id}">${p.name}</option>`);
    });

    window['loadDistricts' + P] = function() {
        const pid = document.getElementById('province' + P).value;
        document.getElementById('district' + P).innerHTML = '<option value="">Select District</option>';
        if (!pid) return;
        fetch(base + '/geo/districts?province_id=' + pid).then(r => r.json()).then(data => {
            const sel = document.getElementById('district' + P);
            data.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.name}</option>`);
        });
    };
    window['loadSectors' + P] = function() {
        const did = document.getElementById('district' + P).value;
        document.getElementById('sector' + P).innerHTML = '<option value="">Select Sector</option>';
        if (!did) return;
        fetch(base + '/geo/sectors?district_id=' + did).then(r => r.json()).then(data => {
            const sel = document.getElementById('sector' + P);
            data.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.name}</option>`);
        });
    };
    window['loadCells' + P] = function() {
        const sid = document.getElementById('sector' + P).value;
        document.getElementById('cell' + P).innerHTML = '<option value="">Select Cell</option>';
        if (!sid) return;
        fetch(base + '/geo/cells?sector_id=' + sid).then(r => r.json()).then(data => {
            const sel = document.getElementById('cell' + P);
            data.forEach(c => sel.innerHTML += `<option value="${c.id}">${c.name}</option>`);
        });
    };
    window['loadVillages' + P] = function() {
        const cid = document.getElementById('cell' + P).value;
        document.getElementById('village' + P).innerHTML = '<option value="">Select Village</option>';
        if (!cid) return;
        fetch(base + '/geo/villages?cell_id=' + cid).then(r => r.json()).then(data => {
            const sel = document.getElementById('village' + P);
            data.forEach(v => sel.innerHTML += `<option value="${v.id}">${v.name}</option>`);
        });
    };
})();
</script>