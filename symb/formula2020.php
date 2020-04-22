<?php
//retrieving the formula if any
$formula=$_POST['formula'];
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script type="text/javascript" src="jquery-3.5.0.min.js"></script>
</head>
<style>
#draggable { width: 150px; height: 150px; padding: 0.5em; }
</style>
<body>

<button id=s1>sample1</button>
<button id=s2>sample2</button>
<button id=s3>sample3</button>
<button id=s4>sample4</button>
<button id=s5>sample5</button>
<form method=POST>
<input type=text id=formula name=formula value='<?php echo $formula; ?>' size=150>
<input type=submit value="analyse formula">
</form>

<script>
$("#s1").click(function(){
  var f='1162.5*5.19*MAX(2.05*f123/f43*f12-f234*f12-f45/12*23,0)+max(f43,4)+f23*(f423+0.5*f65)+f243*f34-f13';
  document.getElementById("formula").value=f;
});
$("#s2").click(function(){
  var f="1/12*(f23+4.6*f12)-f13";
  document.getElementById("formula").value=f;
});
$("#s3").click(function(){
  var f="f23+4.6*f13-12.564";
  document.getElementById("formula").value=f;
});
$("#s4").click(function(){
  var f="1162.5*5.19*max(f7-f11,0)";
  document.getElementById("formula").value=f;
});
$("#s5").click(function(){
  var f="(f43+f23)*(f7-f11)";
  document.getElementById("formula").value=f;
});
</script>

<?php
include $_SERVER['DOCUMENT_ROOT']."/menu.php" ;
$verbose=1;

if (!$formula) exit();

$formula=str_replace('\\','',$formula);
$formula=strtolower($formula);
if ($verbose) print("formula is $formula<br>");
$original=$formula;

// regular expression to recognize a float or int value
// use of ?: not to perturb things in creating references
$Xnbr="(?:[0-9]+\.[0-9]+|[0-9]+)";
// regexp for a feed
$Xf="f\d+";
// regexp for an operator - blank instead of * is not permitted
$Xop="(?:-|\*|\+|\/)";
// regexp for starting a formula with an operator or with nothing
$XSop="(?:-|\+|)";
// regexp for a basic formula, ie something like f12-f43 or 2.056*f42+f45
$Xbf="$XSop(?:$Xnbr$Xop)*$Xf(?:$Xop(?:$Xnbr$Xop)*$Xf(?:$Xop$Xnbr)*)*";
// regexp for a scaling parameter
$Xscaleop="(?:\*|\/)";
$Xscale="$XSop(?:$Xnbr$Xscaleop)*(?:$Xf$Xscaleop)*";

// functions list
// brackets must always be the last function in the list
$functions=[
  ["name"=>"max","f"=>"max\(($Xbf),($Xnbr)\)"],
  ["name"=>"brackets","f"=>"\(($Xbf)\)"],
];

$nbf=count($functions);
if ($verbose) print("we have $nbf function(s) implemented in our library :-)<br>");

//we catch the distinct feed numbers involved in the formula
$feed_ids=[];
$cf=$formula;
while(preg_match("/$Xf/",$cf,$b)){
    $feed_ids[]=substr($b[0],1);
    $cf=str_replace($b[0],"",$cf);
}
if ($verbose){
   print("<pre>");
   print_r($feed_ids);
   print("</pre>");
}


$array= [];
for ($i=0;$i<$nbf;$i++){
  $e=$functions[$i]["name"];
  $f=$functions[$i]["f"];
  if ($verbose) print("checking for function $i named $e<br>");
  while (preg_match("/$f/",$formula,$tab)) {
      if ($verbose) print("success with $formula<br>");
      //we remove the first element of tab which is the complete full match
      //the formula matching /$Xbf/ is therefore tab[0]
      $matched=array_shift($tab);
      $array[]=[
          "scale"=>1,
          "fun"=>$e,
          "formula"=>$tab
      ];
      $index=count($array);
      $formula=str_replace($matched,"func",$formula);
      //$c=[];
      if (preg_match("/($Xscale)func/",$formula,$c)){
          if ($verbose) print("searching for a scale");
          if ($c[1]){
             if ($verbose) print("->we have a scale<br>");
             $array[$index-1]["scale"]=$c[1];
          } else if (verbose) print("->we have nothing<br>");
      }
      $formula=str_replace("$c[1]func","",$formula);
      if ($verbose) print("formula is now only $formula<br>");
  }
}
//checking if we have only a basic formula
if (preg_match("/^$Xbf$/",$formula,$tab)){
    $array[]=[
        "scale"=>1,
        "fun"=>"none",
        "formula"=>$tab
    ];
}
print("<br>");
//we rebuild the formula with what we have extracted
$recf="";
foreach ($array as $a){
    if ($a["scale"]=="1") $scale=""; else $scale=$a["scale"];
    if ($a["fun"]=="max"){
      $recf.="{$scale}max({$a["formula"][0]},{$a["formula"][1]})";
    }
    else if ($a["fun"]=="brackets"){
      $recf.="{$scale}({$a["formula"][0]})";
    }
    else if ($a["fun"]=="none"){
      $recf.="{$scale}{$a['formula'][0]}";
    }
}
print("<pre>");
print_r($array);
print("</pre>");
print("<br>");
print($recf);
print("<br>");
if ($recf==$original) print("OUUUURAY"); else {
  print("STOPPING could not understand your formula SORRY");
  exit();
}
/*
format an array for the formula engine
$b is the result of a preg_match
*/
function ftoa($b){
  $c=[];
  if(sizeof($b)==4) {
    $c[0]="feed";$c[2]=intval(substr($b[3],1));
  } else {
    $c[0]="value";$c[2]=$b[2];
  }
  $c[1]=$b[1];
  if (!$c[1]) $c[1]='+';
  if (!$c[2]) $c[2]=1;
  return $c;
}
print("<br>");
$elements=[];
foreach ($array as $a){
    $element=new stdClass();
    // we analyse the scaling parameter
    $fly=[];
    foreach (preg_split("@(?=(\*|\/))@",$a["scale"]) as $piece) {
      if ($result=preg_match("/($Xop)?($Xnbr)?($Xf)?/",$piece,$b)){
         $c=ftoa($b);
         if($c[2]) $fly[]=$c;
      }
    }
    $element->scale=$fly;
    $element->function=$a["fun"];
    if (count($a["formula"]) > 1) $element->arg2=$a["formula"][1];
    // we analyse the formula
    print("<pre>");
    print_r(preg_split("@(?=(-|\+))@",$a["formula"][0]));
    print("</pre>");
    foreach(preg_split("@(?=(-|\+))@",$a["formula"][0]) as $pieces) {
       if(strlen($pieces)){
         $fly=[];
         foreach(preg_split("@(?=(\*|\/))@",$pieces) as $piece) {
           if ($result=preg_match("/($Xop)?($Xnbr)?($Xf)?/",$piece,$b)) {
              $c=ftoa($b);
              if($c[2]) $fly[]=$c;
           }
         }
         $element->formula[]=$fly;
       }
    }
    $elements[]=$element;
}
print("<pre>");
print_r($elements);
print("</pre>");


?>
<script>
var xnbr = "(?:[0-9]+\\.[0-9]+|[0-9]+)";
// regexp for a feed
var xf = "f\\d+";
// regexp for an operator - blank instead of * is not permitted
var xop = "(?:\\*|\\+|-|\\/)";
// regexp for starting a formula with an operator or with nothing
var xsop = "(?:\\+|-|)";
// regexp for a basic formula, ie something like f12-f43 or 2.056*f42+f45
var xbf = new RegExp(xsop+"(?:"+xnbr+xop+")*"+xf+"(?:"+xop+"(?:"+xnbr+xop+")*"+xf+"(?:"+xop+xnbr+")*)*");

console.log(xbf);
formula="-2190.65*f123*f12+f56-f134";
console.log(formula.match(xbf));
</script>
</body>
</html>
