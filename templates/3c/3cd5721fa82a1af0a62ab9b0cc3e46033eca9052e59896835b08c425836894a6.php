<?php

/* layout.html.twig */
class __TwigTemplate_86f4802c3a8326f57485ba43fab428363c90eb7428e2bd7f0ce37bac15b0dfc7 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
  <meta charset=\"utf-8\"/>
  <title>Slim 3</title>
  <link href='//fonts.googleapis.com/css?family=Lato:300' rel='stylesheet' type='text/css'>
  <style>
    body {
      margin: 50px 0 0 0;
      padding: 0;
      width: 100%;
      font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif;
      text-align: center;
      color: #aaa;
      font-size: 18px;
    }

    h1 {
      color: #719e40;
      letter-spacing: -3px;
      font-family: 'Lato', sans-serif;
      font-size: 100px;
      font-weight: 200;
      margin-bottom: 0;
    }
  </style>
</head>
<body>
  <div id=\"content\">";
        // line 29
        $this->displayBlock('content', $context, $blocks);
        echo "</div>
</body>
</html>
";
    }

    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "layout.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  50 => 29,  20 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("<!DOCTYPE html>
<html>
<head>
  <meta charset=\"utf-8\"/>
  <title>Slim 3</title>
  <link href='//fonts.googleapis.com/css?family=Lato:300' rel='stylesheet' type='text/css'>
  <style>
    body {
      margin: 50px 0 0 0;
      padding: 0;
      width: 100%;
      font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif;
      text-align: center;
      color: #aaa;
      font-size: 18px;
    }

    h1 {
      color: #719e40;
      letter-spacing: -3px;
      font-family: 'Lato', sans-serif;
      font-size: 100px;
      font-weight: 200;
      margin-bottom: 0;
    }
  </style>
</head>
<body>
  <div id=\"content\">{% block content %}{% endblock %}</div>
</body>
</html>
", "layout.html.twig", "/Users/JaceRider/Desktop/terminus_remote/templates/layout.html.twig");
    }
}
