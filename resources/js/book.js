import Alpine from 'alpinejs';
import contact from './book/contact';
import interfaceBook from './book/interface';
import mosaique from './book/mosaique';
import visionneuse from './book/visionneuse';

/*
 * Books aux modeles Ultra-frais et Ultra-zen (resources/views/book/ultra2020).
 * Remplace jQuery 1.12, Semantic UI 2.4, LavaLamp, Magnific Popup et
 * imagesLoaded, charges par le gabarit legacy (2012_web/ultra2020/js/core.js).
 */
[contact, interfaceBook, mosaique, visionneuse].forEach((module) => Alpine.plugin(module));

window.Alpine = Alpine;
Alpine.start();
