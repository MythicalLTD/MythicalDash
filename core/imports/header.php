<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?= $getsettingsdb["linkvertise"] ?>
<link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
<meta name="keywords" content="<?= $getsettingsdb['seo_keywords'] ?>">
<meta name="theme-color" content="<?= $getsettingsdb['seo_color'] ?>">
<meta name="description" content="<?= $getsettingsdb['seo_description'] ?>">
<meta name="og:description" content="<?= $getsettingsdb['seo_description'] ?>">
<meta property="og:title" content="<?= $getsettingsdb['name'] ?>">
<meta property="og:image" content="<?= $getsettingsdb['logo'] ?>">
<link rel="icon" href="<?= $getsettingsdb["logo"] ?>" type="image/png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/plugin/webfont/webfont.min.js"></script>
<script>
	WebFont.load({
		google: {"families":["Lato:300,400,700,900"]},
		custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/fonts.min.css']},
		active: function() {
			sessionStorage.fonts = true;
		}
	});
</script>
<link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/atlantis.css">
<link rel="stylesheet" href="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/css/demo.css">