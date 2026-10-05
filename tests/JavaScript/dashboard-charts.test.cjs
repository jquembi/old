const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');
const source = readFileSync('resources/js/dashboard-charts.js', 'utf8').replace(/^import .*;\r?\n/, '');

function harness(alpineReady) {
    let factory;
    let listener;
    let destroyed = 0;
    const charts = new Map();
    const alpine = { data(name, callback) { assert.equal(name, 'dashboardCharts'); factory = callback; } };
    class Chart {
        constructor(canvas, config) { this.canvas = canvas; this.config = config; charts.set(canvas, this); }
        destroy() { charts.delete(this.canvas); destroyed++; }
        static getChart(canvas) { return charts.get(canvas); }
    }
    const window = alpineReady ? { Alpine: alpine } : {};
    const document = {
        documentElement: { classList: { contains: () => false } },
        addEventListener(event, callback, options) {
            assert.equal(event, 'alpine:init'); assert.equal(options.once, true); listener = callback;
        },
    };
    vm.runInNewContext(source, { window, document, Chart });
    return { window, alpine, charts, factory: () => factory, listener: () => listener, destroyed: () => destroyed };
}

test('registers after Alpine is already available on another page', () => {
    const h = harness(true);
    assert.equal(typeof h.factory(), 'function');
});

test('registers on initial Alpine startup', () => {
    const h = harness(false);
    assert.equal(h.factory(), undefined);
    h.window.Alpine = h.alpine;
    h.listener()();
    assert.equal(typeof h.factory(), 'function');
});

test('renders four charts and releases them across navigation', () => {
    const h = harness(true);
    const canvases = new Map(['Municipios', 'Estados', 'Tendencia', 'Tipos'].map(name => ['#chart' + name + 'Canvas', {}]));
    const data = { labels: [], data: [], instrumentos: [], loteamentos: [], projectos: [] };
    function page() {
        const component = h.factory()(data, data, data, data);
        component.$root = { querySelector: id => canvases.get(id), querySelectorAll: () => [...canvases.values()] };
        component.$nextTick = callback => callback();
        return component;
    }
    const first = page();
    first.init();
    assert.equal(h.charts.size, 4);
    first.renderAllCharts(data, data, data, data);
    assert.equal(h.charts.size, 4);
    assert.equal(h.destroyed(), 4);
    first.destroy();
    assert.equal(h.charts.size, 0);
    const second = page();
    second.init();
    assert.equal(h.charts.size, 4);
    second.destroy();
    assert.equal(h.charts.size, 0);
});
