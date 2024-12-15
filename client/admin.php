<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../css/sidebar.css">
<style>
body {
  margin: 0;
  font-family: "Outfit", sans-serif;
}

.sidebar {
  margin: 0;
  padding: 0;
  width: 200px;
  background-color: #2a3577;
  position: fixed;
  height: 100%;
  overflow: auto;
  border-top-right-radius: 40px;
  border-bottom-right-radius: 40px;
  z-index: 1000;
}

.sidebar a {
  display: block;
  color: black;
  padding: 16px;
  text-decoration: none;
}
 
.sidebar a.active {
  background-color: #04AA6D;
  color: white;
}

.sidebar a:hover:not(.active) {
  background-color: #555;
  color: white;
}

div.content {
  margin-left: 160px;
  padding: 1px 60px;
  height: 1000px;
}

.header {
  background-color: white;
  display: flex;
  justify-content: space-between;
  padding: 20px;
  padding-left:16%;
  position: fixed; /* Makes the header fixed */
  width: 84%; /* Ensures the header spans the full width of the viewport */
  top: 0; /* Fixes the header to the top of the viewport */
  z-index: 900; /* Ensures the header stays above other elements */
}

@media screen and (max-width: 700px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    border-top-right-radius: 0px;
  border-bottom-right-radius: 0px;
  }
  .sidebar a {float: left;}
  div.content {margin-left: 0;}
}

@media screen and (max-width: 400px) {
  .sidebar a {
    text-align: center;
    float: none;
  }
}
</style>
</head>
<body>

<div class="sidebar">
<img src="../images/logo.png" class="logo" style="width:90px" alt="Logo"><br/>
  <a class="active" href="#home">Home</a>
  <a href="#news">News</a>
  <a href="#contact">Contact</a>
  <a href="#about">About</a>
</div>

<div style='color: #546178'>
  <div class='header'><div style='font-size:20px;font-weight:600'>Dashboard</div><div><a href='./notification.php'><i class="fa fa-bell" style="font-size:24px;margin-right:20px;color:#546178"></i></a></div></div>
<div class="content">
  <h2>Responsive Sidebar Example</h2>
  <p>This example use media queries to transform the sidebar to a top navigation bar when the screen size is 700px or less.</p>
  <p>We have also added a media query for screens that are 400px or less, which will vertically stack and center the navigation links.</p>
  <h3>Resize the browser window to see the effect.</h3>
</div>
</div>

</body>
</html>
