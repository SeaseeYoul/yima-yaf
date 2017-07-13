<?php

/* index.html */
class __TwigTemplate_0d60860368f52e48ae1c0de65ecf26a778b89f82c5f0747f71dd4fe4dd68949d extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
\t<meta charset=\"UTF-8\">
\t<title>Document</title>
</head>
<body>
<form id=\"tf\">
            <input type=\"file\" name=\"img\"/>
            <input type=\"text\" name=\"username\"/>
            <input type=\"button\" value=\"提\" onclick=\"test();\"/>
 </form>
<script typet=\"text/javascript\" src=\"http://libs.baidu.com/jquery/1.9.1/jquery.min.js\"></script>
<script type=\"text/javascript\">
\t        function test(){
            var form = new FormData(document.getElementById(\"tf\"));
//             var req = new XMLHttpRequest();
//             req.open(\"post\", \"\${pageContext.request.contextPath}/public/testupload\", false);
//             req.send(form);
            \$.ajax({
                url:\"/api/index/up\",
                type:\"post\",
                data:form,
                processData:false,
                contentType:false,
                success:function(data){
                    console.log(data);
                },
                error:function(e){
                    alert(\"错误！！\");
                    window.clearInterval(timer);
                }
            });        
        }
</script>
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "index.html";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "index.html", "C:\\git\\yima-yaf\\application\\modules\\Api\\views\\index.html");
    }
}
