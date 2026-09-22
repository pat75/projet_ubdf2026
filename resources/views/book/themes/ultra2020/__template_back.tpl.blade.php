{{-- Porte depuis 2011_html_pages_v2/ultra2020/__template_back.tpl.php (_outils/porter_gabarits.py) --}}



<!-- popup expert css -->
<div class="ui popup bottom left popup_css">
    <div class="header">CSS expert</div>
    <div class="content">
        <div class="ui form">
            <div class="field">
                <textarea id="css_data"></textarea>
            </div>

            <div class="ui button admin_btn_save">Enregistrer</div>
        </div>
    </div>
</div>








<!-- popup link social -->
<div class="ui popup bottom left popup_social">
    <div class="header">Social links</div>
    <div class="content">
        <div class="ui form">
            <div class="ui labeled input">
                <div class="ui label">
                    http://
                </div>
                <input type="text" placeholder="Facebook" name="link_facebook">
            </div>
            <div class="ui labeled input">
                <div class="ui label">
                    http://
                </div>
                <input type="text" placeholder="Instagram" name="link_instagram">
            </div>
            <div class="ui labeled input">
                <div class="ui label">
                    http://
                </div>
                <input type="text" placeholder="Pinterest" name="link_pinterest">
            </div>
            <div class="ui labeled input">
                <div class="ui label">
                    http://
                </div>
                <input type="text" placeholder="Twitter" name="link_twitter">
            </div>
            <div class="ui labeled input">
                <div class="ui label">
                    http://
                </div>
                <input type="text" placeholder="Linkedin" name="link_linkedin">
            </div>
            <br/>
            <button class="ui button admin_btn_save" type="submit">Enregistrer</button>

        </div>
    </div>
</div>




<!-- modal nav -->
<div class="ui fluid+ popup transition hidden modal_nav">

    <div class="header">Menu</div>
    <div class="content">
        <div class="ui form">
            <div class="ui input">
                <input type="text" placeholder="Portfolio" name="name_portfolio">
            </div>
            <div class="ui input">
                <input type="text" placeholder="Page" name="name_page">
            </div>
            <div class="ui input">
                <input type="text" placeholder="Contact" name="name_contact">
            </div>

            <br/>
            <button class="ui button admin_btn_save" type="submit">Enregistrer</button>

        </div>
    </div>
</div>






<!-- modal portfolio open project -->
<div class="ui modal modal_portfolio_open">
   <div class="image content">
        <img class="image">
        <div class="description">
            <p></p>
        </div>
    </div>
</div>









<!-- modal portfolio -->
<div class="ui fullscreen modal modal_portfolio">
    <i class="close icon"></i>
    <div class="header">
        Portfolio
    </div>
    <div class="scrolling content_iframe">
    </div>
</div>

<!-- modal pages -->
<div class="ui fullscreen modal modal_page">
    <i class="close icon"></i>
    <div class="header">
        Pages
    </div>
    <div class="scrolling content_iframe">
    </div>
</div>



<!-- tpl iframe -->
<script id="tpl_admin_modal_iframe" type="text/x-handlebars">
     <iframe id="inlineFrameExample"
                class="modal_popup_iframe"
                style="height: -webkit-fill-available;"
                title="Inline Frame Example"
                width="100%"
                height="100%"
                src="[[url_portfolio]]"
        >
        </iframe>
</script>










<!-- tpl edit   /save/trash -->
<script id="tpl_admin_control_edit-trash" type="text/x-handlebars">
    <label class="admin_control_segment ">
            <div class="mini ui icon buttons admin_control admin_mode_edit">
                <button class="mini ui attached icon button         admin_btn_trash     "  data-tooltip="Supprimer" data-inverted=""><i class="icon trash"></i></button>
                <button class="mini ui right attached icon button   admin_btn_save      "  data-tooltip="Enregistrer" data-inverted=""><i class="icon check "></i></button>
                <button class="mini ui attached icon button         admin_btn_edit      "  data-tooltip="Editer" data-inverted=""><i class="icon pencil alternate"></i></button>
                <button class="mini ui attached icon button         admin_btn_add       "  data-tooltip="Ajouter"      data-inverted=""><i class="icon plus"></i></button>
            </div>
    </label>
</script>

<!-- tpl edit   /save -->
<script id="tpl_admin_control_edit" type="text/x-handlebars">
    <label class="admin_control_segment ">
        <div class="mini ui icon buttons admin_control admin_mode_edit">
            <button class="mini ui right attached icon button   admin_btn_save      "  data-tooltip="Enregistrer" data-inverted=""><i class="icon check "></i></button>
            <button class="mini ui attached icon button         admin_btn_edit      "  data-tooltip="Editer" data-inverted=""><i class="icon pencil alternate"></i></button>
            <button class="mini ui attached icon button         admin_btn_add       "  data-tooltip="Ajouter"      data-inverted=""><i class="icon plus"></i></button>
        </div>
    </label>
</script>

<!-- tpl edit-icon  /save/trash -->
<script id="tpl_admin_control_edit-icon-trash" type="text/x-handlebars">
    <label class="admin_control_segment ">
        <div class="mini ui icon buttons admin_control admin_mode_edit">
            <button class="mini ui attached icon button        admin_btn_trash      "  data-tooltip="Supprimer"    data-inverted=""><i class="icon trash"></i></button>
            <button class="mini ui right attached icon button  admin_btn_save       "  data-tooltip="Enregistrer"  data-inverted=""><i class="icon check"></i></button>
            <button class="mini ui attached icon button        admin_btn_edit_icon  "  data-tooltip="Editer"       data-inverted=""><i class="icon pencil alternate"></i></button>
            <button class="mini ui attached icon button        admin_btn_add        "  data-tooltip="Ajouter"      data-inverted=""><i class="icon plus"></i></button>
        </div>
     </label>
</script>
