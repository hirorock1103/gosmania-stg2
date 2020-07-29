<!--header-->
<header class="main-header">
	<a href="./" class="logo"><b></b></a>
	<!-- Header Navbar: style can be found in header.less -->
	<nav class="navbar navbar-static-top" role="navigation">
		<!-- Sidebar toggle button-->
		<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</a>
		<div class="top_nav_right">
			<!-- <div style="margin-top:3px">
				<a href="schedule_today.php?delay" class="top_nav_alert">
					<span class="header_user_name">報告がないスケジュールが13那由多件あります。</span>
				</a> 
			</div> -->
			<div>
				<a class="header_text" href="#">
					<i class="fa fa-user"></i>
					<span class="header_user_name"><?php echo $_SESSION[SESSION_BASE_NAME]['login_info']['Ad_Name']; ?></span>
				</a>
			</div>
			<div>
				<a class="header_text" href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i>
				</a>
			</div>
		</div>
	</nav>
</header>
<!--header-->

