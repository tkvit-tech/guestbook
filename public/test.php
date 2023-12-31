<?php
function arrayDiff($a, $b){
    foreach($a as $key => &$val){
        if (in_array($val, $b)) {unset($a[$key]);}
        //$val = $val+1;
    }
    //print_r($a);
    $a = array_values($a);
}
function repeatStr($n, $str)
{
    $result = '';
    for($i = 0; $i<$n; $i++){
        $result .= $str;
    }
    return $result;
}
function moveZeros(array $items): array
{
    // your code here
    $cut = [];
    foreach ($items as $k=>$v){
      if ($v === 0 && is_integer($v)) {
          $cut[] = 0;
          unset($items[$k]);
      }
    }
    return array_merge($items, $cut);
}

function remove_char(string $s): string {
    // Write your code here
    if (strlen($s)<3){
        $s = '';
    }
    else{
        $s = substr($s,1,-1);
    }
    return $s;
}

function enough($cap, $on, $wait) {
    // your code here
    return (($cap - $on) >= $wait) ? 0 : ($wait - ($cap - $on));
}
//echo enough(20,5,5);
function validBraces($braces){
    static $cnt = 0;
    /*
    $braces = str_split($braces);
    foreach ($braces as $k => $v){
        echo $v."<br>";
    }*/
    for ($i = 0; $i < strlen($braces); $i++){
        $char = substr($braces, $i, 1);
        echo $char.'##'.$cnt.'<br>';
        if ($char == '('){
            $cnt--;
            validBraces(substr($braces, $i+1));
        }
        $cnt++;

        if ($cnt > 0) die('false'.$cnt);
    }

}
validBraces('(())((()())())');

//echo remove_char('w');
//print_r(moveZeros([false,1,0,1,2,0,1,3,"a"]));

//echo repeatStr(3,'hello');
//$a = [1,2,3];
//unset($a[1]);
//print_r(array_values($a));
//print_r($a);
//$b = [1];
//arrayDiff($a, $b);
//print_r($a);