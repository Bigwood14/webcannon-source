<html>
  <head>
    <title><?php echo $_SERVER['SERVER_NAME'] ?></title>
    <style type="text/css">
body
{
background: #ececec;
font-family: arial,verdana;
color: #333;
}
h1
{
font-size: 18px;
border-bottom: 1px solid #666;
}
label
{
	font-weight: normal;
}
</style>

<h1><?php echo $_SERVER['SERVER_NAME'] ?></h1>
<form action="" method="post">
User: <input type="text" name="username" />
<br />
Pass: <input type="password" name="password" />
<input type="submit" name="login" value="Go" />
<form>

</body>
</html>
