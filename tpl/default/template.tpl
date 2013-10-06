<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="robots" content="index, follow">
<meta name="keywords" content="{PS_KEYWORDS}">
<meta name="title" content="{PS_TITLE}">
<meta name="author" content="Administrator">
<meta name="description" content="{PS_DESCRIPTION}">
<meta name="google-site-verification" content="{PS_GOOGLE_KEY}" />
<title>{PS_TITLE}</title>
<link rel="stylesheet" href="{PS_TPL}style.css" type="text/css" />
</head>
<body>
<div id="container">
	<div id="header">
    	<h1><a href="/">{US_TITLE}</a></h1>
        <h2>{US_SLOGAN}</h2>
        <div class="clear"></div>
    </div>
    <div id="nav">
    	<ul>
			{PS_MENU}
        </ul>
    </div>
    <div id="body">
		<div id="content">
			<h2>{PV_TITLE}</h2>
			{PV_CONTENT}
        </div>
        
        <div class="sidebar">
            <ul>	
				<li>
					{UV_BLOCK_1}
				</li>
			   <li>
                    {US_R_MENU}
                </li>
                
                <li>
                    {UV_BLOCK_ABOUT}
                </li>
                
                <li>
                	<h3>Search</h3>
                    <ul>
                    	<li>
                            <form method="get" class="searchform" action="" >
                                <p>
                                    <input type="text" size="20" value="" name="s" class="s" />
                                    <input type="submit" class="searchsubmit formbutton" value="Search" />
                                </p>
                            </form>	
						</li>
					</ul>
                </li>
                
                <li>
					{UV_BLOCK_2}
                </li>
                
            </ul> 
        </div>
    	<div class="clear"></div>
    </div>
    <div id="footer">
        <div class="footer-content">
			{US_LIST_1}
			{US_LIST_2}
			{US_LIST_3}
			
            <div class="clear"></div>
        </div>
        <div class="footer-bottom">
			{US_FOOTER}
         </div>
    </div>
</div>
</body>
</html>
