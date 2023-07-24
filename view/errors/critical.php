<?php 
http_response_code(500); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>mythicaldash | Critical Error</title>
  <link rel="icon" type="image/png" href="https://avatars.githubusercontent.com/u/117385445">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <style>.card-body,.card-header{padding:30px;color:#fff}body{overflow:hidden;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-image:url('https://wallpaperaccess.com/full/8642981.jpg');background-color:#34495e;background-blend-mode:overlay;background-size:cover;background-position:center}.container{display:flex;justify-content:center;align-items:center;height:100vh}.card{max-width:800px;border:none;border-radius:20px;box-shadow:0 2px 6px rgba(0,0,0,.3)}.card-header{background-color:#e74c3c;border-radius:20px 20px 0 0;font-weight:700;display:flex;align-items:center;border-bottom:none}.card-header::before{content:"⚠️";font-size:28px;margin-right:12px}.card-body{background-color:#222831;overflow-y:auto;max-height:500px}.error-message{color:#ff6363;font-size:24px;line-height:1.5;font-weight:700;margin-top:0}.code-box{background-color:#2c3e50;border-radius:8px;padding:20px;margin-top:30px;max-height:250px;overflow-y:auto}.code-line{font-family:"Courier New",Courier,monospace;color:#fff;margin:0}.error-text{margin-top:30px;font-size:18px;line-height:1.5}.error-text a{color:#e74c3c;font-weight:700;text-decoration:none}.error-text a:hover{text-decoration:underline}#particles-js{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-1}.card-body::-webkit-scrollbar{display:none}.card{overflow:hidden}  </style>
</head>
<body>
  <div id="particles-js"></div>
  <div class="container">
    <div class="card">
      <div class="card-header">
        <h1 class="h1">Critical Error</h1>
      </div>
      <div class="card-body">
        <p class="error-message">We are sorry, but something went wrong.</p>
        <div class="code-box"><!-- TO USE A NEW LINE USE &NewLine; -->
            <pre><code class="code-line"><?php if(isset($_GET['e'])) { echo $_GET['e'];} else { echo 'No error'; }?></code></pre>
          </div>                  
        <p class="error-text">We apologize for the inconvenience. Please try again later.</p>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {particlesJS('particles-js', {"particles":{"number":{"value":160,"density":{"enable":true,"value_area":800}},"color":{"value":"#ffffff"},"shape":{"type":"circle","stroke":{"width":0,"color":"#000000"},"polygon":{"nb_sides":5},"image":{"src":"img/github.svg","width":100,"height":100}},"opacity":{"value":1,"random":true,"anim":{"enable":true,"speed":1,"opacity_min":0,"sync":false}},"size":{"value":3,"random":true,"anim":{"enable":false,"speed":4,"size_min":0.3,"sync":false}},"line_linked":{"enable":false,"distance":150,"color":"#ffffff","opacity":0.4,"width":1},"move":{"enable":true,"speed":1,"direction":"none","random":true,"straight":false,"out_mode":"out","bounce":false,"attract":{"enable":false,"rotateX":600,"rotateY":600}}},"interactivity":{"detect_on":"canvas","events":{"onhover":{"enable":true,"mode":"bubble"},"onclick":{"enable":true,"mode":"push"},"resize":true},"modes":{"grab":{"distance":0,"line_linked":{"opacity":1}},"bubble":{"distance":250,"size":0,"duration":2,"opacity":0,"speed":3},"repulse":{"distance":400,"duration":0.4},"push":{"particles_nb":4},"remove":{"particles_nb":2}}},"retina_detect":true});});
  </script>
</body>
</html>