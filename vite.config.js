import fs from 'node:fs';
import os from 'node:os';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/*
 * Le site est servi par Valet en HTTPS. Si le serveur de developpement
 * repond en HTTP, le navigateur bloque ses ressources (contenu mixte) et
 * la feuille de surcharge n'est jamais appliquee. On reutilise donc le
 * certificat que Valet a deja genere pour ce domaine.
 */
const domaine = 'ubdf2026.ultra-book.name';
const certificats = `${os.homedir()}/.config/valet/Certificates/${domaine}`;
const httpsValet = fs.existsSync(`${certificats}.key`)
    ? { key: fs.readFileSync(`${certificats}.key`), cert: fs.readFileSync(`${certificats}.crt`) }
    : undefined;

export default defineConfig({
    plugins: [
        laravel({
            // ubdf.css porte les surcharges du portail ; app.css/app.js sont
            // reserves a la refonte Tailwind de la phase 9.
            input: ['resources/css/ubdf.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Le site est servi par Valet en HTTPS : sans hote explicite, le
        // navigateur refuse les ressources du serveur de developpement.
        host: '127.0.0.1',
        https: httpsValet,
        cors: true,
        hmr: { host: '127.0.0.1', protocol: httpsValet ? 'wss' : 'ws' },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
