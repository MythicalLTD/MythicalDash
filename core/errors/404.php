<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | NOT FOUND</title>
    <link rel="shortcut icon" type="https://ignition-development.xyz/cdn/slake/images/logo.svg">
    <style>
        * {
    font-family: Google sans, Arial;
  }
  
  html, body {
    margin: 0;
    padding: 0;
  }
  
  .flex-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    color: white;
    animation: colorSlide 15s cubic-bezier(0.075, 0.82, 0.165, 1) infinite;
  }
  .flex-container .text-center {
    text-align: center;
  }
  .flex-container .text-center h1,
  .flex-container .text-center h3 {
    margin: 10px;
    cursor: default;
  }
  .flex-container .text-center h1 .fade-in,
  .flex-container .text-center h3 .fade-in {
    animation: fadeIn 2s ease infinite;
  }
  .flex-container .text-center h1 {
    font-size: 8em;
    transition: font-size 200ms ease-in-out;
    border-bottom: 1px dashed white;
  }
  .flex-container .text-center h1 span#digit1 {
    animation-delay: 200ms;
  }
  .flex-container .text-center h1 span#digit2 {
    animation-delay: 300ms;
  }
  .flex-container .text-center h1 span#digit3 {
    animation-delay: 400ms;
  }
  .flex-container .text-center button {
    border: 1px solid white;
    background: transparent;
    outline: none;
    padding: 10px 20px;
    font-size: 1.1rem;
    font-weight: bold;
    color: white;
    text-transform: uppercase;
    transition: background-color 200ms ease-in;
    margin: 20px 0;
  }
  .flex-container .text-center button:hover {
    background-color: white;
    color: #555;
    cursor: pointer;
  }
  
  @keyframes colorSlide {
    0% {
      background-color: #152a68;
    }
    25% {
      background-color: royalblue;
    }
    50% {
      background-color: seagreen;
    }
    75% {
      background-color: tomato;
    }
    100% {
      background-color: #152a68;
    }
  }
  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    100% {
      opacity: 1;
    }
  }
    </style>
</head>
<body>
<div class="flex-container">
  <div class="text-center">
    <h1>
      <span class="fade-in" id="digit1">4</span>
      <span class="fade-in" id="digit2">0</span>
      <span class="fade-in" id="digit3">4</span>
    </h1>
    <h3 class="fadeIn">NOT FOUND</h3>
    <a href="/"><button type="button" name="button">Return To Home</button></a>
  </div>
</div>
</body>
</html>