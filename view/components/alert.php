<?php
if (isset($_GET['e'])) {
    ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <?= htmlspecialchars($_GET['e']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
}
?>
<?php
if (isset($_GET['s'])) {
    ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <?= htmlspecialchars($_GET['s']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
}
?>