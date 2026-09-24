import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import Paragraph from '@editorjs/paragraph';
import ImageTool from '@editorjs/image';

/*
 * Editeur de texte des pages, moteur « redactor_bloc » (config('pages.editeur_texte'),
 * App\Livewire\Espace\Pages) : editeur par blocs maison base sur Editor.js,
 * pour trois types de blocs (titre, paragraphe, image), l'equivalent par
 * blocs du moteur « redactor » de resources/js/espace.js.
 *
 * Le contenu part vers Livewire sous forme de blocs structures
 * (App\Services\Espace\RenduBlocsPage les traduit en HTML a l'enregistrement),
 * pas de HTML brut : `surChangement` recoit `{blocks: [...]}.blocks`.
 */
window.espacePageEditorBlocs = (el, valeurInitiale, surChangement, urls) => {
    const jeton = document.querySelector('meta[name="csrf-token"]')?.content;

    const editeur = new EditorJS({
        holder: el,
        placeholder: 'Écrivez le contenu de la page…',
        data: { blocks: Array.isArray(valeurInitiale) && valeurInitiale.length ? valeurInitiale : [] },
        tools: {
            header: { class: Header, config: { levels: [2, 3, 4], defaultLevel: 2, placeholder: 'Titre' } },
            paragraph: { class: Paragraph, inlineToolbar: ['bold', 'italic', 'link'] },
            image: {
                class: ImageTool,
                config: {
                    // Reponse de App\Http\Controllers\Espace\PageImageController::store
                    // ({filelink, id}) adaptee au format attendu par l'outil ({success, file: {url}}) :
                    // meme depot que le moteur Redactor (App\Services\Espace\DepotImagePage),
                    // meme dossier img_cms/ du createur, meme bibliotheque.
                    uploader: {
                        async uploadByFile(fichier) {
                            const donnees = new FormData();
                            donnees.append('file', fichier);
                            donnees.append('_token', jeton);

                            const reponse = await fetch(urls.upload, { method: 'POST', body: donnees, headers: { Accept: 'application/json' } });
                            const json = await reponse.json();

                            return reponse.ok
                                ? { success: 1, file: { url: json.filelink, id: json.id } }
                                : { success: 0 };
                        },
                    },
                },
            },
        },
        onChange: async (api) => {
            const donnees = await api.saver.save();
            surChangement(donnees.blocks);
        },
    });

    return editeur;
};
