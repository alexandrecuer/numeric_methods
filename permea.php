<html>
   <head>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <script type="text/javascript" src="jquery-1.11.3.min.js"></script>
   </head>
   <style>
   #draggable { width: 150px; height: 150px; padding: 0.5em; }
   </style>
   <body>
      <h1>Decker-Brent</h1>
      <?php 
      $mathfunc=["acos","asin","atan","sin","cos","tan","pow","PI","E","exp","log","sqrt","asin","acos"];
      $mathfunc_js=["acos(x)","asin(x)","atan(x)","sin(x)","cos(x)","tan(x)","pow(x,3)","PI","E","exp(x)","log(x)","sqrt(x)","asin(x)","acos(x)"];
      for ($i=0;$i<sizeof($mathfunc);$i++)
          echo("<button id=$mathfunc[$i]>$mathfunc_js[$i]</button>");
      echo("<br>");
      ?>
      Enter function expression: <input id="f" type="text"  style="width:400px"><button id='clear'>clear</button><br>
      graphic resolution : <input id="pas" type="text"  style="width:50px" value='0.001'>
      a : <input id="xmin" type="text"  style="width:50px" value='-20.0'>
      b : <input id="xmax" type="text"  style="width:50px" value='4'>
      <button id='fzero'>calculate zero</button>
      <input id="zero" type="text"  style="width:250px">
      In <input id="nbit" type="text"  style="width:150px"> Iterations
      <div id="graph" style="height:300px; width:100%; position:relative;"></div>
      <script src="permea.js" type="text/javascript"></script> 
      <!--[if IE]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
      <script language="javascript" type="text/javascript" src="flot/jquery.flot.min.js"></script>
      <script language="javascript" type="text/javascript" src="flot/jquery.flot.selection.min.js"></script>
      <script language="javascript" type="text/javascript" src="flot/jquery.flot.touch.min.js"></script>
      <script language="javascript" type="text/javascript" src="flot/jquery.flot.togglelegend.min.js"></script>
      <script language="javascript" type="text/javascript" src="flot/jquery.flot.time.min.js"></script>
      <script language="javascript" type="text/javascript" src="flot/jquery.flot.stack.min.js"></script>
      <script language="javascript" type="text/javascript" src="flot/jquery.flot.canvas.js"></script>
      <script language="javascript" type="text/javascript" src="flot/plugin/saveAsImage/lib/base64.js"></script>
      <script language="javascript" type="text/javascript" src="flot/plugin/saveAsImage/lib/canvas2image.js"></script>
      <script language="javascript" type="text/javascript" src="flot/plugin/saveAsImage/jquery.flot.saveAsImage.js"></script>
      <script id="source" language="javascript" type="text/javascript">
      var formula;
      
      function get_exp() {
          if (document.getElementById("f").value) {
              formula = document.getElementById("f").value;
              console.log(formula);
          }
      }
      
      function f(x) {
          return (eval(formula));
      }

      function plotnfind () {
          get_exp();
          var xmin = Number($("#xmin").val());
          var xmax = Number($("#xmax").val());
          
          var XY =[];
          var pas = Number($("#pas").val());
          //var pas = 0.001;
          var x = xmin;
          var i = 0;
          while (x < xmax){
              XY[i]=[];
              XY[i][0]=x;XY[i][1]=f(x);x+=pas;i+=1;
          }

          $.plot("#graph", [XY]);
          var result = {};
          result = fzero(f,xmin,xmax);
          console.log(result);
          var zero = result.zero;
          if (typeof(zero) == "string") {
              document.getElementById("zero").value = zero;
          } else {
              document.getElementById("zero").value = (Math.round(zero*100))/100;
              document.getElementById("nbit").value = result.nbit;
          }
          
      }
      
      $("#fzero").click(function () {plotnfind();});
      $("#clear").click(function () {document.getElementById("f").value = "";});
      <?php
      for ($i=0;$i<sizeof($mathfunc);$i++){
          $a = $mathfunc[$i];$b = $mathfunc_js[$i];
          echo("$('#{$a}').click(function () {document.getElementById('f').value+='Math.{$b}';});");
      }
      ?>
      </script>
   </body>
</html>