<?php
$path = wa('shop')->getDataPath('plugins/csvcli/', false, 'shop', true);
foreach ( array('csvcli.csv', 'csvcli.csv.snapshot', 'sets.php') as $k ){
    $file = $path . $k;

    if ( file_exists($file) ){
        $move = $path . '0/' . $k;
        waFiles::move($file, $move);
    }
}