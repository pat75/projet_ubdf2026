/*
 * Tableau de bord des visites (/espace/statistiques), repris du /stats de
 * les-illustrateurs : onglets 7 / 30 / 90 jours, courbe des visites,
 * anneau par surface, barres des 12 derniers mois.
 *
 * Chart.js n'est charge que sur cette page (import dynamique : Vite en
 * fait un fichier a part).
 */
export default function statistiques(parJour, parMois, surfaces) {
    let graphes = {};

    const jeton = (nom) => getComputedStyle(document.documentElement).getPropertyValue(`--color-ub-${nom}`).trim();

    return {
        jours: 30,

        get dates() {
            return Object.keys(parJour).slice(-this.jours);
        },

        get visites() {
            return this.dates.map((d) => Object.values(parJour[d]).reduce((a, b) => a + b, 0));
        },

        get totalPeriode() {
            return this.visites.reduce((a, b) => a + b, 0);
        },

        get moyenne() {
            return Math.round(this.totalPeriode / this.jours);
        },

        get meilleurJour() {
            const max = Math.max(...this.visites);
            return max > 0 ? { n: max, date: this.libelleDate(this.dates[this.visites.indexOf(max)]) } : null;
        },

        get parSurface() {
            return Object.keys(surfaces).map((s) => this.dates.reduce((t, d) => t + (parJour[d][s] ?? 0), 0));
        },

        get periode() {
            return `${this.libelleDate(this.dates[0])} – ${this.libelleDate(this.dates.at(-1))}`;
        },

        libelleDate(date) {
            return new Date(`${date}T12:00:00`).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
        },

        nombre(n) {
            return n.toLocaleString('fr-FR');
        },

        async init() {
            const { default: Chart } = await import('chart.js/auto');

            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.color = jeton('texte3');

            const accent = jeton('accent');
            const grille = jeton('filet');
            const bulle = { backgroundColor: jeton('texte'), padding: 10, cornerRadius: 2, displayColors: false };

            graphes.visites = new Chart(this.$refs.visites, {
                type: 'line',
                data: {
                    labels: this.dates.map((d) => this.libelleDate(d)),
                    datasets: [{
                        data: this.visites,
                        borderColor: accent,
                        backgroundColor: jeton('accent-fond'),
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: accent,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: bulle },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 8, maxRotation: 0 } },
                        y: { beginAtZero: true, grid: { color: grille }, border: { display: false }, ticks: { precision: 0 } },
                    },
                },
            });

            graphes.surfaces = new Chart(this.$refs.surfaces, {
                type: 'doughnut',
                data: {
                    labels: Object.values(surfaces),
                    datasets: [{
                        data: this.parSurface,
                        backgroundColor: [accent, jeton('messages'), jeton('sortie'), jeton('texte4')],
                        borderWidth: 0,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, padding: 16 } }, tooltip: bulle },
                },
            });

            graphes.mois = new Chart(this.$refs.mois, {
                type: 'bar',
                data: {
                    labels: Object.keys(parMois),
                    datasets: [{ data: Object.values(parMois), backgroundColor: jeton('texte'), borderRadius: 2, maxBarThickness: 32 }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: bulle },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } },
                        y: { beginAtZero: true, grid: { color: grille }, border: { display: false }, ticks: { precision: 0 } },
                    },
                },
            });

            this.$watch('jours', () => this.actualiser());
        },

        actualiser() {
            graphes.visites.data.labels = this.dates.map((d) => this.libelleDate(d));
            graphes.visites.data.datasets[0].data = this.visites;
            graphes.visites.update();

            graphes.surfaces.data.datasets[0].data = this.parSurface;
            graphes.surfaces.update();
        },

        destroy() {
            Object.values(graphes).forEach((g) => g.destroy());
            graphes = {};
        },
    };
}
