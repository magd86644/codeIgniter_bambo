<?php if ((count($errors) > 0)) { ?>
    <div class="alert alert-danger alert-dismissible show" role=" alert">
        <ul class="mb-0">
            <?php foreach ($errors as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php } ?>