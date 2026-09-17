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
            input: ['resources/css/ubdf.css', 'resources/css/book.css', 'resources/js/app.js'],
            /*
             * Recharge la page des qu'une vue, une route ou un composant
             * change. « true » ne couvre pas app/View/Components ni les
             * fichiers de configuration, alors que les deux pilotent le
             * rendu du portail.
             */
            refresh: [
                'resources/views/**',
                'resources/css/**',
                'resources/js/**',
                'routes/**',
                'config/**',
                'app/View/Components/**',
                'app/Http/Controllers/**',
                'public/js/**',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        /*
         * Ecoute sur toutes les interfaces. Se lier au domaine directement
         * ne reservait que l'adresse IPv6 (::1) vers laquelle il resout en
         * premier, et un navigateur qui tentait 127.0.0.1 n'obtenait rien.
         */
        host: '::',

        /*
         * Les ressources sont donc annoncees sous le nom du site, pour
         * lequel le certificat de Valet est emis : servi depuis une autre
         * adresse, ce certificat ferait rejeter aussi bien la feuille de
         * style que le websocket de rechargement.
         */
        origin: `https://${domaine}:5273`,

        https: httpsValet,
        cors: true,

        /*
         * Port fixe : le 5173 par defaut est occupe par le pont de Valet,
         * Vite basculait donc sur 5174 a chaque demarrage et public/hot
         * pouvait pointer vers une instance morte. strictPort fait echouer
         * le demarrage plutot que de deriver en silence.
         */
        port: 5273,
        strictPort: true,

        hmr: {
            host: domaine,
            protocol: httpsValet ? 'wss' : 'ws',
        },

        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
