<?php
include("config/conexion_2.php");
?>
<!DOCTYPE html>
<html lang="es">

<head>

  <meta charset="UTF-8">
  <meta http-equiv="content-language" content="es-mx">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="img/logo.ico" />
  <link rel="apple-touch-icon" href="img/logo.ico" />
  <title>Reporte de indicadores
  </title>

  <!-- Bootstrap -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style_nav.css" rel="stylesheet">
  <link href="css/modulo_informe.css" rel="stylesheet">
  <link href="css/efectos.css" rel="stylesheet">
  <!-- Se agregan los css de la Libreria dataTables -->
  <link href="assets/datatables/jquery.dataTables.min.css" rel="stylesheet">
  <link href="assets/datatables/buttons.dataTables.min.css" rel="stylesheet">

</head>

<body>
  <nav class="navbar navbar-default navbar-fixed-top">
    <?php include('nav.php'); ?>
  </nav>


  <div class="container">
    <div class="content">

      <div class="table-filter">
        <div class="row">
          <div class="col-md-12">
            <br>
            <form class="form-inline" method="get">
              <div class="form-group">
                <label>Dependencia:
                </label>
                <select name="filtro_2" class="form-control" onchange="form.submit()">
                  <option selected disabled value="">Filtrar por Dependencia</option>
                  <?php $filtro2 = (isset($_GET['filtro_2']) ? strtolower($_GET['filtro_2']) : NULL);  ?>
                  <?php
                  $result = $con->query(
                    "SELECT distinct  dependencia from indicadores_municipales where (validado is not null and validado<>0) and  usuario = (SELECT usuario from usuarios where institucion=(select institucion from usuarios where usuario='$usuario') and tipo_usuario=8);"
                  );

                  echo '<option value="todos" ';
                  if ($filtro2 == 'todos') {
                    echo 'selected';
                  }
                  echo '>Todos los elementos</option>';
                  if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {
                      echo '<option value="' . $row['dependencia'] . '"';
                      if ($filtro2 == $row['dependencia']) {
                        echo 'selected';
                      }
                      echo '>' . $row['dependencia'] . '</option>';
                    }
                  }
                  ?>
                </select>
              </div>
            </form>
          </div>
        </div>
        <br>
      </div>

      <!--Se agrega una tabla con las funcionalidades de la libreria datatables para su mejor manipulación-->
      <table id="indicadores" class="display table-responsive table-striped table-hover" style="width:100%">
        <thead>
          <tr>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;">Indicador</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;">Dependencia</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2016</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2017</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2018</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2019</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2020</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2021</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2022</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2023</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;"> 2024</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;">Meta 2024</th>
            <th style="background-color: #AC1643; color:#fff; text-align:center; border-bottom: 1px solid #fff;">Periodicidad</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($filtro2) {
            if ($filtro2 == "todos") {


              $segmento = mysqli_query($con, "SELECT * from indicadores_municipales where (validado is not null and validado<>0) and  usuario = (SELECT usuario from usuarios where institucion=(select institucion from usuarios where usuario='$usuario') and tipo_usuario=8)	ORDER BY id_indicador");
            } else {
              $segmento = mysqli_query($con, "SELECT * FROM indicadores_municipales where dependencia='$filtro2' and (validado is not null and validado<>0) and  usuario = (SELECT usuario from usuarios where institucion=(select institucion from usuarios where usuario='$usuario') and tipo_usuario=8)	ORDER BY id_indicador ASC");
            }
          } else {
            $segmento = mysqli_query($conexion, "SELECT * FROM indicadores_municipales where (validado is not null and validado<>0) and  usuario = (SELECT usuario from usuarios where institucion=(select institucion from usuarios where usuario='$usuario') and tipo_usuario=8)	ORDER BY id_indicador");
            // ahora obtenemos el segmento paginado que corresponde a esta pagina
          }


          if (mysqli_num_rows($segmento) == 0) {
            echo '<tr><td colspan="8">No hay datos.</td></tr>';
          } else {
            $no = 1;
            if ($segmento) {

              while ($row = mysqli_fetch_assoc($segmento)) {
                echo '<tr>';
                switch ($row['periodicidad']) {
                  case 'Anual':
                    // $datos_tiempo = $conexion->prepare("SELECT * FROM ri_anual where id_indicador='".$row['id_indicador']."' and año_temporalidad=(select year (now())) ");                            
                    $datos_tiempo = $conexion->prepare("SELECT * FROM ri_anual where id_indicador='" . $row['id_indicador'] . "' and  año_temporalidad=(select max(año_temporalidad) where id_indicador='" . $row['id_indicador'] . "') ");
                    $datos_tiempo->execute();
                    $resultadot = $datos_tiempo->get_result();
                    $rowt = $resultadot->fetch_assoc();
                    //while ($row_semestral = $resultado_semestral->fetch_assoc()){
                    if (isset($rowt["dato_anual"]) and $rowt["dato_anual"] != null and $rowt["dato_anual"] > 0) {
                      $vt = $rowt['dato_anual'];
                      $va = $rowt['año_temporalidad'];
                    } else {
                      $vt = 'N/D';
                      $va = 'N/D';
                    }

                    //  echo '<td>'.$no.'</td>';
                    echo '<td>' . $row['indicador'] . '</td>';
                    echo '<td>' . $row['dependencia'] . '</td>';
                    
                    if ($va == '2016') {

                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2017') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2018') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2019') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2020') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2021') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2022') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2023') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2024') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                    } else {
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    }

                    echo '<td  style="text-align:center;">' . $row['meta_2024'] . '</td>';
                    echo '<td>' . $row['periodicidad'] . '</td>';
                    echo '</tr>';
                    $no++;
                    break;
                  case 'Semestral':
                    $datos_tiempo = $conexion->prepare("SELECT * FROM ri_semestral where id_indicador='" . $row['id_indicador'] . "' and año_temporalidad=(select max(año_temporalidad) where id_indicador='" . $row['id_indicador'] . "') ");
                    $datos_tiempo->execute();
                    $resultadot = $datos_tiempo->get_result();
                    $rowt = $resultadot->fetch_assoc();

                    if (isset($rowt["dato_1semestre"]) and $rowt["dato_1semestre"] != null and $rowt["dato_1semestre"] != ''  and ($rowt["dato_2semestre"] == null or $rowt["dato_2semestre"] = '')) {
                      $vt = $rowt['dato_1semestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_2semestre"]) and $rowt["dato_2semestre"] != null and $rowt["dato_2semestre"] != '' and ($rowt["dato_1semestre"] == null or $rowt["dato_1semestre"] = '')) {
                      $vt = $rowt['dato_2semestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_2semestre"]) and $rowt["dato_2semestre"] != null and $rowt["dato_2semestre"] != '' and isset($rowt["dato_1semestre"]) and $rowt["dato_1semestre"] != null and $rowt["dato_1semestre"] != '') {
                      $vt = $rowt['dato_2semestre'];
                      $va = $rowt['año_temporalidad'];
                    } else {
                      $vt = 'N/Dx';
                      $va = 'N/D';
                    }

                    echo '<td>' . $row['indicador'] . '</td>';
                    echo '<td>' . $row['dependencia'] . '</td>';

                    if ($va == '2016') {

                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2017') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2018') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2019') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2020') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2021') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2022') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2023') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2024') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                    } else {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    }
                    echo '<td style="text-align:center;">' . $row['meta_2024'] . '</td>';

                    echo '<td>' . $row['periodicidad'] . '</td>';
                    echo '</tr>';
                    $no++;
                    break;
                  case 'Bimestral':
                    $datos_tiempo = $conexion->prepare("SELECT * FROM ri_bimestral where id_indicador='" . $row['id_indicador'] . "' and año_temporalidad=(select max(año_temporalidad) where id_indicador='" . $row['id_indicador'] . "') ");
                    $datos_tiempo->execute();
                    $resultadot = $datos_tiempo->get_result();
                    $rowt = $resultadot->fetch_assoc();

                    if (isset($rowt["dato_1bimestre"]) and $rowt["dato_1bimestre"] != null and ($rowt["dato_2bimestre"] == null or $rowt["dato_2bimestre"] == '') and ($rowt["dato_3bimestre"] == null or $rowt["dato_3bimestre"] == '') and ($rowt["dato_4bimestre"] == null or $rowt["dato_4bimestre"] == '') and ($rowt["dato_5bimestre"] == null or $rowt["dato_5bimestre"] == '') and ($rowt["dato_6bimestre"] == null or $rowt["dato_6bimestre"] == '')) {
                      $vt = $rowt['dato_1bimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_2bimestre"]) and $rowt["dato_2bimestre"] != null and ($rowt["dato_1bimestre"] == null or $rowt["dato_1bimestre"] == '') and ($rowt["dato_3bimestre"] == null or $rowt["dato_3bimestre"] == '') and ($rowt["dato_4bimestre"] == null or $rowt["dato_4bimestre"] == '') and ($rowt["dato_5bimestre"] == null or $rowt["dato_5bimestre"] == '') and ($rowt["dato_6bimestre"] == null or $rowt["dato_6bimestre"] == '')) {
                      $vt = $rowt['dato_2bimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_3bimestre"]) and $rowt["dato_3bimestre"] != null and ($rowt["dato_1bimestre"] == null or $rowt["dato_1bimestre"] == '') and ($rowt["dato_2bimestre"] == null or $rowt["dato_2bimestre"] == '') and ($rowt["dato_4bimestre"] == null or $rowt["dato_4bimestre"] == '') and ($rowt["dato_5bimestre"] == null or $rowt["dato_5bimestre"] == '') and ($rowt["dato_6bimestre"] == null or $rowt["dato_6bimestre"] == '')) {
                      $vt = $rowt['dato_3bimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_4bimestre"]) and $rowt["dato_4bimestre"] != null and ($rowt["dato_1bimestre"] == null or $rowt["dato_1bimestre"] == '') and ($rowt["dato_2bimestre"] == null or $rowt["dato_2bimestre"] == '') and ($rowt["dato_3bimestre"] == null or $rowt["dato_3bimestre"] == '') and ($rowt["dato_5bimestre"] == null or $rowt["dato_5bimestre"] == '') and ($rowt["dato_6bimestre"] == null or $rowt["dato_6bimestre"] == '')) {
                      $vt = $rowt['dato_4bimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_5bimestre"]) and $rowt["dato_5bimestre"] != null and ($rowt["dato_1bimestre"] == null or $rowt["dato_1bimestre"] == '') and ($rowt["dato_2bimestre"] == null or $rowt["dato_2bimestre"] == '') and ($rowt["dato_4bimestre"] == null or $rowt["dato_4bimestre"] == '') and ($rowt["dato_3bimestre"] == null or $rowt["dato_3bimestre"] == '') and ($rowt["dato_6bimestre"] == null or $rowt["dato_6bimestre"] == '')) {
                      $vt = $rowt['dato_5bimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_6bimestre"]) and $rowt["dato_6bimestre"] != null and ($rowt["dato_1bimestre"] == null or $rowt["dato_1bimestre"] == '') and ($rowt["dato_2bimestre"] == null or $rowt["dato_2bimestre"] == '') and ($rowt["dato_4bimestre"] == null or $rowt["dato_4bimestre"] == '') and ($rowt["dato_5bimestre"] == null or $rowt["dato_5bimestre"] == '') and ($rowt["dato_3bimestre"] == null or $rowt["dato_3bimestre"] == '')) {
                      $vt = $rowt['dato_6bimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_1bimestre"]) and $rowt["dato_1bimestre"] != null and isset($rowt["dato_2bimestre"]) and $rowt["dato_2bimestre"] != null and isset($rowt["dato_3bimestre"]) and $rowt["dato_3bimestre"] != null and isset($rowt["dato_4bimestre"]) and $rowt["dato_4bimestre"] != null and isset($rowt["dato_5bimestre"]) and $rowt["dato_5bimestre"] != null and isset($rowt["dato_5bimestre"]) and $rowt["dato_5bimestre"] != null and isset($rowt["dato_6bimestre"]) and $rowt["dato_6bimestre"] != null) {
                      $vt = $rowt['dato_6bimestre'];
                      $va = $rowt['año_temporalidad'];
                    } else {
                      $vt = 'N/D';
                      $va = 'N/D';
                    }

                    echo '<td>' . $row['indicador'] . '</td>';
                    echo '<td>' . $row['dependencia'] . '</td>';
                   
                    if ($va == '2016') {

                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2017') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2018') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2019') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2020') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2021') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2022') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2023') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2024') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                    } else {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    }
                    echo '<td style="text-align:center;">' . $row['meta_2024'] . '</td>';
 
                    echo '<td>' . $row['periodicidad'] . '</td>';
                    echo '</tr>';
                    $no++;
                    break;
                  case 'Trimestral':
                    $datos_tiempo = $conexion->prepare("SELECT * FROM ri_trimestral where id_indicador='" . $row['id_indicador'] . "' and año_temporalidad=(select max(año_temporalidad) where id_indicador='" . $row['id_indicador'] . "') ");
                    $datos_tiempo->execute();
                    $resultadot = $datos_tiempo->get_result();
                    $rowt = $resultadot->fetch_assoc();

                    if (isset($rowt["dato_1trimestre"]) and $rowt["dato_1trimestre"] != null and ($rowt["dato_3trimestre"] == null or $rowt["dato_3trimestre"] == '') and  ($rowt["dato_4trimestre"] == null or $rowt["dato_4trimestre"] == '') and ($rowt["dato_2trimestre"] == null or $rowt["dato_2trimestre"] == '')) {
                      $vt = $rowt['dato_1trimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_2trimestre"]) and $rowt["dato_2trimestre"] != null and ($rowt["dato_3trimestre"] == null or $rowt["dato_3trimestre"] == '') and  ($rowt["dato_4trimestre"] == null or $rowt["dato_4trimestre"] == '') and ($rowt["dato_1trimestre"] == null or $rowt["dato_1trimestre"] == '')) {
                      $vt = $rowt['dato_2trimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_3trimestre"]) and $rowt["dato_3trimestre"] != null and ($rowt["dato_4trimestre"] == null or $rowt["dato_4trimestre"] == '') and ($rowt["dato_2trimestre"] == null or $rowt["dato_2trimestre"] == '') and ($rowt["dato_1trimestre"] == null or $rowt["dato_1trimestre"] == '')) {
                      $vt = $rowt['dato_3trimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_4trimestre"]) and $rowt["dato_4trimestre"] != null and ($rowt["dato_3trimestre"] == null or $rowt["dato_3trimestre"] == '') and ($rowt["dato_2trimestre"] == null or $rowt["dato_2trimestre"] == '') and ($rowt["dato_1trimestre"] == null or $rowt["dato_1trimestre"] == '')) {
                      $vt = $rowt['dato_4trimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_4trimestre"]) and $rowt["dato_4trimestre"] != null and isset($rowt["dato_3trimestre"]) and $rowt["dato_3trimestre"] != null and isset($rowt["dato_2trimestre"]) and $rowt["dato_2trimestre"] != null and isset($rowt["dato_1trimestre"]) and $rowt["dato_1trimestre"] != null) {
                      $vt = $rowt['dato_4trimestre'];
                      $va = $rowt['año_temporalidad'];
                    } else {
                      $vt = 'N/D';
                      $va = 'N/D';
                    }

                    echo '<td>' . $row['indicador'] . '</td>';
                    echo '<td>' . $row['dependencia'] . '</td>';
                    
                    if ($va == '2016') {


                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2017') {


                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2018') {


                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2019') {


                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2020') {


                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2021') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2022') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2023') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2024') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                    } else {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    }
                    echo '<td style="text-align:center;">' . $row['meta_2024'] . '</td>';

                    echo '<td>' . $row['periodicidad'] . '</td>';
                    echo '</tr>';
                    $no++;
                    break;
                  case 'Cuatrimestral':
                    $datos_tiempo = $conexion->prepare("SELECT * FROM ri_cuatrimestral where id_indicador='" . $row['id_indicador'] . "' and año_temporalidad=(select max(año_temporalidad) where id_indicador='" . $row['id_indicador'] . "') ");
                    $datos_tiempo->execute();
                    $resultadot = $datos_tiempo->get_result();
                    $rowt = $resultadot->fetch_assoc();

                    if (isset($rowt["dato_1cuatrimestre"]) and $rowt["dato_1cuatrimestre"] != null and ($rowt["dato_2cuatrimestre"] == null or $rowt["dato_2cuatrimestre"] == '') and ($rowt["dato_3cuatrimestre"] == null or $rowt["dato_3cuatrimestre"] == '')) {
                      $vt = $rowt['dato_1cuatrimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_2cuatrimestre"]) and $rowt["dato_2cuatrimestre"] != null and ($rowt["dato_1cuatrimestre"] == null or $rowt["dato_1cuatrimestre"] == '') and ($rowt["dato_3cuatrimestre"] == null or $rowt["dato_3cuatrimestre"] == '')) {
                      $vt = $rowt['dato_2cuatrimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_3cuatrimestre"]) and $rowt["dato_3cuatrimestre"] != null and ($rowt["dato_2cuatrimestre"] == null or $rowt["dato_2cuatrimestre"] == '') and ($rowt["dato_1cuatrimestre"] == null or $rowt["dato_1cuatrimestre"] == '')) {
                      $vt = $rowt['dato_3cuatrimestre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_3cuatrimestre"]) and $rowt["dato_3cuatrimestre"] != null and isset($rowt["dato_2cuatrimestre"]) and $rowt["dato_2cuatrimestre"] != null and isset($rowt["dato_1cuatrimestre"]) and $rowt["dato_1cuatrimestre"] != null) {
                      $vt = $rowt['dato_3cuatrimestre'];
                      $va = $rowt['año_temporalidad'];
                    } else {
                      $vt = 'N/D';
                      $va = 'N/D';
                    }

                    echo '<td>' . $row['indicador'] . '</td>';
                    echo '<td>' . $row['dependencia'] . '</td>';
                   
                    if ($va == '2016') {


                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2017') {


                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2018') {


                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2019') {


                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2020') {

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2021') {
                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2022') {
                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2023') {
                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2024') {
                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                    } else {
                      /*echo '<td  style="text-align:center;">N/D</td>'; 
                  echo '<td  style="text-align:center;">N/D</td>'; 
                  echo '<td  style="text-align:center;">N/D</td>'; 
                  echo '<td  style="text-align:center;">N/D</td>'; 
                  echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    }
                    echo '<td style="text-align:center;">' . $row['meta_2024'] . '</td>';
                    // echo '<td>'.$row['proxima_actualizacion'].'</td>';  
                    echo '<td>' . $row['periodicidad'] . '</td>';
                    echo '</tr>';
                    $no++;
                    break;
                  case 'Mensual':
                    $datos_tiempo = $conexion->prepare("SELECT * FROM ri_mensual where id_indicador='" . $row['id_indicador'] . "' and año_temporalidad=(select max(año_temporalidad) where id_indicador='" . $row['id_indicador'] . "') ");
                    $datos_tiempo->execute();
                    $resultadot = $datos_tiempo->get_result();
                    $rowt = $resultadot->fetch_assoc();
                    //while ($row_semestral = $resultado_semestral->fetch_assoc()){
                    if (isset($rowt["dato_enero"]) and $rowt["dato_enero"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_enero'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_febrero"]) and $rowt["dato_febrero"] != null and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_febrero'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_marzo"]) and $rowt["dato_marzo"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_marzo'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_abril"]) and $rowt["dato_abril"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_abril'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_mayo"]) and $rowt["dato_mayo"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_mayo'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_junio"]) and $rowt["dato_junio"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_junio'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_julio"]) and $rowt["dato_julio"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_julio'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_agosto"]) and $rowt["dato_agosto"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_agosto'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_septiembre"]) and $rowt["dato_septiembre"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_septiembre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_octubre"]) and $rowt["dato_octubre"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_octubre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_noviembre"]) and $rowt["dato_noviembre"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '') and ($rowt["dato_diciembre"] == null or $rowt["dato_diciembre"] == '')) {
                      $vt = $rowt['dato_noviembre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_diciembre"]) and $rowt["dato_diciembre"] != null and ($rowt["dato_febrero"] == null or $rowt["dato_febrero"] == '') and ($rowt["dato_marzo"] == null or $rowt["dato_marzo"] == '') and ($rowt["dato_abril"] == null or $rowt["dato_abril"] == '') and ($rowt["dato_mayo"] == null or $rowt["dato_mayo"] == '') and ($rowt["dato_junio"] == null or $rowt["dato_junio"] == '') and ($rowt["dato_julio"] == null or $rowt["dato_julio"] == '') and ($rowt["dato_agosto"] == null or $rowt["dato_agosto"] == '') and ($rowt["dato_septiembre"] == null or $rowt["dato_septiembre"] == '') and ($rowt["dato_octubre"] == null or $rowt["dato_octubre"] == '')  and ($rowt["dato_noviembre"] == null or $rowt["dato_noviembre"] == '') and ($rowt["dato_enero"] == null or $rowt["dato_enero"] == '')) {
                      $vt = $rowt['dato_diciembre'];
                      $va = $rowt['año_temporalidad'];
                    } elseif (isset($rowt["dato_diciembre"]) and $rowt["dato_diciembre"] != null and isset($rowt["dato_enero"]) and $rowt["dato_enero"] != null and isset($rowt["dato_febrero"]) and $rowt["dato_febrero"] != null and isset($rowt["dato_marzo"]) and $rowt["dato_marzo"] != null and isset($rowt["dato_abril"]) and $rowt["dato_abril"] != null and isset($rowt["dato_mayo"]) and $rowt["dato_mayo"] != null and isset($rowt["dato_junio"]) and $rowt["dato_junio"] != null and isset($rowt["dato_julio"]) and $rowt["dato_julio"] != null and isset($rowt["dato_agosto"]) and $rowt["dato_agosto"] != null and isset($rowt["dato_septiembre"]) and $rowt["dato_septiembre"] != null and isset($rowt["dato_octubre"]) and $rowt["dato_octubre"] != null and isset($rowt["dato_noviembre"]) and $rowt["dato_noviembre"] != null) {
                      $vt = $rowt['dato_diciembre'];
                      $va = $rowt['año_temporalidad'];
                    } else {
                      $vt = 'N/D';
                      $va = 'N/D';
                    }
                    // echo '<td>'.$row['id_indicador'].'</td>';
                    // echo '<td>'.$no.'</td>';
                    echo '<td>' . $row['indicador'] . '</td>';
                    echo '<td>' . $row['dependencia'] . '</td>';
                    /* if( $va=='2010')
                                     {
                                       echo '<td  style="text-align:center;">'.$vt.'</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                       
                                     }
                                     elseif( $va=='2011')
                                     {
                                      
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">'.$vt.'</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                     }
                                     elseif( $va=='2012')
                                     {
                                      
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">'.$vt.'</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                     }
                                     elseif( $va=='2013')
                                     {
                                       
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">'.$vt.'</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                     }
                                     elseif( $va=='2014')
                                     {
                                      
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">'.$vt.'</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                     }
                                     elseif( $va=='2015')
                                     {
                                       
                                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>';
                                       echo '<td  style="text-align:center;">'.$vt.'</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                       echo '<td  style="text-align:center;">N/D</td>'; 
                                     }*/
                    if ($va == '2016') {

                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2017') {

                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2018') {

                      /*  echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2019') {

                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2020') {

                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2021') {
                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2022') {
                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2023') {
                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    } elseif ($va == '2024') {
                      /* echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; 
                   echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';

                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">' . $vt . '</td>';
                    } else {
                      /*echo '<td  style="text-align:center;">N/D</td>'; 
                  echo '<td  style="text-align:center;">N/D</td>'; 
                  echo '<td  style="text-align:center;">N/D</td>'; 
                  echo '<td  style="text-align:center;">N/D</td>'; 
                  echo '<td  style="text-align:center;">N/D</td>'; */
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                      echo '<td  style="text-align:center;">N/D</td>';
                    }
                    echo '<td style="text-align:center;">' . $row['meta_2024'] . '</td>';
                    // echo '<td>'.$row['proxima_actualizacion'].'</td>';  
                    echo '<td>' . $row['periodicidad'] . '</td>';
                    echo '</tr>';
                    $no++;
                    break;
                }
              }
            }
          }
          ?>
        </tbody>
      </table>
      <br>


    </div>
  </div>

  <div class="container-fluid justify-content-center text-light">
    <footer>
      <div class="container">
        <div class="row my-4 justify-content-center py-4">
          <br>
          <div class="col-12">
            <div class="row">
              <div class="col-xl-8 col-md-4 col-sm-4 col-12 my-auto mx-auto a">
                <h3 class="mb-3 mb-lg-4 bold-text">Gobierno del Estado <br> de Puebla</h3>
              </div>
              <div class="col-xl-2 col-md-4 col-sm-4 col-12">
                <h6 class="mb-3 mb-lg-4 bold-text "><b>Sitios de Interés</b></h6>
                <ul class="list-unstyled">
                  <a href="https://puebla.gob.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                    <li>Gobierno del Estado de Puebla</li>
                  </a>
                  <a href="http://ceigep.puebla.gob.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                    <li>CEIGEP</li>
                  </a>
                  <a href="http://agenda2030.puebla.gob.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                    <li>Agenda 2030</li>
                  </a>
                </ul>
              </div>
              <div class="col-xl-2 col-md-4 col-sm-4 col-12">
                <br><br>
                <ul class="list-unstyled">
                  <a href="https://planeader.puebla.gob.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                    <li>Portal Planeación</li>
                  </a>
                  <a href="https://informe.puebla.gob.mx/" target="blank_" style="text-decoration:none; color:#fff;">
                    <li>Informe de Gobierno</li>
                  </a>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <br>
      <center>
        <p>&copy; Secretaría de Planeación y Finanzas - Subsecretaría de Planeación <?php echo date("Y"); ?></p
          </center>
    </footer>
  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/filtro.js"></script>
  <!--Se agrega las librerias js de la libreria dataTables-->
  <script src="assets/datatables/jquery-3.5.1.js"></script>
  <script src="assets/datatables/jquery.dataTables.min.js"></script>
  <script src="assets/datatables/dataTables.buttons.min.js"></script>
  <script src="assets/datatables/jszip.min.js"></script>
  <script src="assets/datatables/pdfmake.min.js"></script>
  <script src="assets/datatables/vfs_fonts.js"></script>
  <script src="assets/datatables/buttons.html5.min.js"></script>
  <!--Fin libreria dataTables-->
  <script>
    $(document).ready(function() {
      // Append a caption to the table before the DataTables initialisation

      var titulo = '<?php
                    $datos = $conexion->prepare("SELECT * from municipios2 where desc_municipio = (select institucion from usuarios where usuario='$usuario');");
                    $datos->execute();
                    $resultado = $datos->get_result();
                    $row = $resultado->fetch_assoc();
                    //while ($row_semestral = $resultado_semestral->fetch_assoc()){
                    if (isset($row["desc_municipio"]) and $row["desc_municipio"] != null)
                      echo 'Reporte de indicadores de ' . $row['desc_municipio'] . '';
                    ?>';
      // alert (titulo);
      // $('#indicadores').css("width", "90%");
      $('#indicadores').append('<caption style="caption-side: bottom">Generado con SPED</caption>');
      $('#indicadores').DataTable({
        dom: 'Bfrtip',

        buttons: [{
            extend: 'copyHtml5',
            title: titulo
          },
          {
            extend: 'excelHtml5',
            title: titulo
          },
          {
            extend: 'csvHtml5',
            title: titulo
          },
          {
            extend: 'pdfHtml5',
            orientation: 'landscape',
            alignment: 'center',
            customize: function(doc) {
              doc.styles.title = {
                color: 'black',
                fontSize: '20',
                //background: '#F9EBD7',
                alignment: 'center'
              }
              //pageMargins [left, top, right, bottom]           
              //doc.pageMargins = [ 100, 20, 100, 20 ];
              doc.content.forEach(function(item) {
                if (item.table) {
                  // Set width for 3 columns
                  // item.table.widths = [70,70,30,30,30,30,30,30,30,30,30,30,30,30,30,30,70] 
                  item.table.widths = [70, 70, 50, 50, 50, 50, 50, 50, 50, 50, 50, 70]
                }
              });
            },
            title: titulo
          }
          /* { extend: 'pdfHtml5',  title: titulo, 
                        image: 'img/logo_sped.png',
                        width: 150,
		                  	height: 150
                 }*/


          /*{
                extend: 'pdf',
                messageTop: 'The information in this table is copyright to Sirius Cybernetics Corp.'
            }*/
          /*   { extend: 'copyHtml5', footer: true },
             { extend: 'excelHtml5', footer: true },
             { extend: 'csvHtml5', footer: true },
             { extend: 'pdfHtml5', footer: true }*/
        ]
      });
    });
  </script>

</body>

</html>