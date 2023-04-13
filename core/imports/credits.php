<style>

.footer {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 70px; /* Set to desired footer height */
  z-index: 9999; 
}

</style>
<footer class="footer">
				<div class="container-fluid">
					<nav class="pull-left">
						<ul class="nav">
								<a class="nav-link" href="https://atoro.tech">
								&copy; 2023 <?= $getsettingsdb["name"]?>  - Powered by AtoroTech</a>
								</a>
						</ul>
					</nav>
					<div class="copyright ml-auto">
                        <span>v2.0</span>
                        <span>-</span>
						<span id="loadtime"></span>					
					</div>				
				</div>
				<style>
					    .space2 {
        margin-left: 5px;
    }
				</style>
			</footer>