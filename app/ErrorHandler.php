<?php
namespace MythicalDash;

class ErrorHandler
{
    public static function Error($title, $text)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.mythicalsystems.me/problem?authKey=AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F&project=mythicaldash&type=error&title=" . $title . "&message=" . $text,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "User-Agent: insomnia/8.2.0"
            ],
        ]);

        curl_exec($curl);
    }

    public static function Warning($title, $text)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.mythicalsystems.me/problem?authKey=AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F&project=mythicaldash&type=warning&title=" . $title . "&message=" . $text,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "User-Agent: insomnia/8.2.0"
            ],
        ]);

        curl_exec($curl);
    }
    public static function Critical($title, $text)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.mythicalsystems.me/problem?authKey=AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F&project=mythicaldash&type=critical&title=" . $title . "&message=" . $text,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "User-Agent: insomnia/8.2.0"
            ],
        ]);

        curl_exec($curl);
    }
    public static function ShowCritical($message)
    {
        ErrorHandler::Critical("Automated error report", $message);
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>MythicalDash - Critical Error</title>
            <link rel="icon" type="image/png" href="https://avatars.githubusercontent.com/u/117385445">
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
            <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
            <link rel="stylesheet" href="https://unpkg.com/simplebar@5.3.4/dist/simplebar.min.css">
            <style>
                body {
                    overflow: hidden;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-image: url(https://wallpaperaccess.com/full/8642981.jpg);
                    background-color: #34495e;
                    background-blend-mode: overlay;
                    background-size: cover;
                    background-position: center;
                    color: #fff
                }

                body.loading {
                    overflow: hidden
                }

                .container {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh
                }

                .card {
                    max-width: 800px;
                    border: none;
                    border-radius: 20px;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, .3);
                    overflow: hidden
                }

                .card-header {
                    background-color: #e74c3c;
                    border-radius: 20px 20px 0 0;
                    font-weight: 700;
                    display: flex;
                    align-items: center;
                    border-bottom: none
                }

                .card-header::before {
                    content: "⚠️";
                    font-size: 28px;
                    margin-right: 12px
                }

                .card-body {
                    background-color: #222831;
                    overflow-y: auto;
                    max-height: 500px
                }

                .error-message {
                    color: #ff6363;
                    font-size: 24px;
                    line-height: 1.5;
                    font-weight: 700;
                    margin-top: 0
                }

                .code-box {
                    background-color: #2c3e50;
                    border-radius: 8px;
                    padding: 20px;
                    margin-top: 30px;
                    max-height: 250px;
                    overflow-y: auto
                }

                .cline {
                    font-family: "Courier New", Courier, monospace;
                    color: #fff;
                    margin: 0
                }

                .error-text {
                    margin-top: 30px;
                    font-size: 18px;
                    line-height: 1.5
                }

                .error-text a {
                    color: #e74c3c;
                    font-weight: 700;
                    text-decoration: none
                }

                .error-text a:hover {
                    text-decoration: underline
                }

                #particles-js {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: -1
                }

                .preloader {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 9999;
                    opacity: 1;
                    background: #222831;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    transition: opacity 2s ease-in-out
                }

                .loader {
                    border: 5px solid #f3f3f3;
                    border-top: 5px solid #3498db;
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    animation: spin 2s linear infinite
                }

                .loading-text {
                    color: #fff;
                    font-size: 24px;
                    margin-top: 20px
                }

                @keyframes spin {
                    0% {
                        transform: rotate(0deg)
                    }

                    100% {
                        transform: rotate(360deg)
                    }
                }

                @keyframes backgroundTransition {
                    0% {
                        background: #000
                    }

                    25% {
                        background: #111
                    }

                    50% {
                        background: #000
                    }

                    100% {
                        background: #111
                    }
                }

                @keyframes fadeOut {
                    0% {
                        opacity: 1
                    }

                    100% {
                        opacity: 0;
                        display: none
                    }
                }
            </style>
        </head>

        <body class="loading">
            <div class="preloader">
                <div class="loader"></div>
                <div class="loading-text">Kindly hold for a moment as we gather the error report.</div>
            </div>
            <div id="particles-js"></div>
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h1 class="h1">Critical Error</h1>
                    </div>
                    <div class="card-body">
                        <p class="error-message">
                            We are sorry, but something went wrong.
                        </p>
                        <div class="code-box" data-simplebar>
                            <code class="cline">
                            <?php
                            if (isset($message)) {
                                http_response_code(500);
                                $error = htmlspecialchars($message);
                                $errorLines = explode(PHP_EOL, $error);
                                foreach ($errorLines as $line) {
                                    $trimmedLine = ltrim($line);
                                    if (!empty($trimmedLine)) {
                                        echo $trimmedLine . PHP_EOL;
                                    }
                                }
                            } else {
                                echo 'No error';
                            }
                            ?>
                        </code>
                        </div>
                        <p class="error-text">
                            We apologize for the inconvenience. Please report this to the site administrator.
                        </p>
                    </div>
                </div>
            </div>
            <script>document.addEventListener("DOMContentLoaded", function () { setTimeout(function () { !function e() { let n = document.querySelector(".preloader"); n.style.opacity = "0", setTimeout(() => { n.style.display = "none", document.body.classList.remove("loading") }, 3500) }() }, 3500), particlesJS("particles-js", { particles: { number: { value: 160, density: { enable: !0, value_area: 800 } }, color: { value: "#ffffff" }, shape: { type: "circle", stroke: { width: 0, color: "#000000" }, polygon: { nb_sides: 5 }, image: { src: "img/github.svg", width: 100, height: 100 } }, opacity: { value: 1, random: !0, anim: { enable: !0, speed: 1, opacity_min: 0, sync: !1 } }, size: { value: 3, random: !0, anim: { enable: !1, speed: 4, size_min: .3, sync: !1 } }, line_linked: { enable: !1, distance: 150, color: "#ffffff", opacity: .4, width: 1 }, move: { enable: !0, speed: 1, direction: "none", random: !0, straight: !1, out_mode: "out", bounce: !1, attract: { enable: !1, rotateX: 600, rotateY: 600 } } }, interactivity: { detect_on: "canvas", events: { onhover: { enable: !0, mode: "bubble" }, onclick: { enable: !0, mode: "push" }, resize: !0 }, modes: { grab: { distance: 0, line_linked: { opacity: 1 } }, bubble: { distance: 250, size: 0, duration: 2, opacity: 0, speed: 3 }, repulse: { distance: 400, duration: .4 }, push: { particles_nb: 4 }, remove: { particles_nb: 2 } } }, retina_detect: !0 }) });</script>
            <script src="https://unpkg.com/simplebar@5.3.4/dist/simplebar.min.js"></script>
            <script>
                new SimpleBar(document.querySelector('[data-simplebar]'));
            </script>
        </body>

        </html>
        <?php
        $output = ob_get_clean();
        die($output);
    }
}
?>