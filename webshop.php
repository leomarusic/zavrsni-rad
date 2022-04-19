<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="slike/servislogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Akshar:wght@500&family=Arimo:wght@600&family=Bebas+Neue&family=Noto+Sans:wght@500&family=Oswald&family=Oxygen&family=Poppins&display=swap" rel="stylesheet">
</head>
<body>
<?php
include 'functions.php';
$conn=OpenCon();
$page = $_GET["page"];
session_start();
?>
<div>
    <table class="selection">
        <tbody>
        <tr>
            <td><a href="index.php">Početna</a></td>
            <td><a href="cjenik.php">Cjenik</a></td>
            <td><a href="webshopreset.php">Web Shop</a></td>
        </tr>
        </tbody>
    </table>
</div>
<div class="spacer" style="background-image: url('slike/Website Images/webshopselection.svg')"></div>
<div class="okvirshop">
    <div class="filters">
        <form method="post" action="">
            <div class="trazilica">
                <div class="trazilicanaslov">
                    <h2>Traži po imenu&ensp;</h2>
                </div>
                <div class="search-container">
                    <label for="search"></label><input type="text" placeholder="Ime uređaja..." id="search" name="search">
                </div>
            </div>
            <br>
            <h2>Traži po cijeni</h2>
            <div class="cijena">
                <label class="down" for="mincijena">Od</label><input type="number" min="0" maxlength="8" name="mincijena" id="mincijena" oninput="if (this.value.length > this.maxLength){ this.value = this.value.slice(0, this.maxLength);}">
                <label class="down" for="maxcijena">Do</label><input type="number" min="0" maxlength="8" name="maxcijena" id="maxcijena" oninput="if (this.value.length > this.maxLength){ this.value = this.value.slice(0, this.maxLength);}">
                <span class="down">kn</span>
            </div>
            <br>
            <div class="orderby">
                <label for="order">Sortiraj:</label>
                <select id="order" name="order">
                    <option value=""></option>
                    <option value="ORDER BY Cijena ASC">Jeftinije prema skupljem</option>
                    <option value="ORDER BY Cijena DESC">Skuplje prema jeftinijem</option>
                    <option value="ORDER BY Ime ASC">Od A do Z</option>
                    <option value="ORDER BY Ime DESC">Od Z do A</option>
                    <option value="ORDER BY DatumDodano ASC">Najnovije</option>
                    <option value="ORDER BY DatumDodano DESC">Najstarije</option>
                </select>
            </div>
            <br>
            <div class="izborpro">
                <?php
                $prod = mysqli_query($conn, "SELECT DISTINCT Proizvodac FROM `proizvod` WHERE 1");
                $array = [];
                foreach ($prod as $row)
                {
                    $array[] = $row['Proizvodac'];
                }
                $len=sizeof($array);
                for ($x = 0; $x < $len; $x++) {
                    $element =
                        '
                            <input type="checkbox" id="' . $array[$x] . '" name="proizvodac[]" value="' . $array[$x] . '" >
                            <label for="' . $array[$x] . '">' . $array[$x] . '</label><br>
                        ';
                    echo $element;
                }
                ?>
            </div>
            <br>
            <div class="pretrazi">
                <input type="submit" id="pretrazi" name="pretrazi" value="Pretraži">
                <input type="submit" name="reset" value="Reset">
            </div>
        </form>
        <?php
        if(isset($_POST["reset"])){
            QueryDestroy();
            header("location: webshop.php?page=1");
        }
        if(isset($_POST["pretrazi"])) {
            $_SESSION["filters"] = 1;
            if(isset($_POST["search"])){
                $serime=htmlspecialchars($_POST["search"]);
                if($serime==null) {
                    $sql[] = " Ime LIKE '%' ";
                }
                else {
                    $sql[] = " Ime LIKE '%$serime%' ";
                }
            }
            if(isset($_POST["mincijena"])) {
                $sermincijena=htmlspecialchars($_POST["mincijena"]);
                if($sermincijena==null)
                {
                    $sql[] = " Cijena BETWEEN 0 ";
                }
                else {
                    $sql[] = " Cijena BETWEEN $sermincijena ";
                }
            }
            if(isset($_POST["maxcijena"])) {
                $sermaxcijena=htmlspecialchars($_POST["maxcijena"]);
                if($sermaxcijena==null)
                {
                    $sql[] = " 999999999 ";
                }
                else {
                    $sql[] = " $sermaxcijena ";
                }
            }
            if(!empty($_POST['proizvodac'])) {
                foreach($_POST['proizvodac'] as $value){
                    $sqlpro[] = " '$value' ";
                }
            }
            if(isset($_POST["order"])){
                $order = $_POST["order"];
                if($order == ""){
                    $queryorder = "ORDER BY ID";
                }
                else{
                    $queryorder = $_POST["order"];
                }
            }
            if (!empty($sql)) {
                if (empty($sqlpro)) {
                    $sqlpro[] = " SELECT Proizvodac FROM proizvod ";
                }
                $query = 'SELECT * FROM proizvod WHERE ' . implode(' AND ', $sql) . 'AND Proizvodac IN (' . implode(' , ',$sqlpro) . ') ' . $queryorder;
                $_SESSION["query"] = $query;
            }
            header("location: webshop.php?page=1");
        }
        ?>
    </div>
    <div class="navig">
        <?php
            if(empty($page) || $page <= 0)
            {
                header("location: webshop.php?page=1");
            }

            if($_SESSION["filters"]==1){
            $query= $_SESSION["query"];
            }
            else{
                $query="SELECT * FROM proizvod WHERE 1";
            }
            $broj = mysqli_query($conn, $query);
            $brojred = mysqli_num_rows($broj);
            $brojstr = ceil($brojred/9);

            $rangestart=($page-1)*9;
            $query = $query . ' LIMIT '.$rangestart.' , 9 ';
            $stmt = mysqli_query($conn, $query);


        ?>
        <script>
            function PageMinus(){
                if(<?php echo $page; ?>-1 > 0){
                    window.location="webshop.php?page=<?php echo $page-1; ?>";
                }
            }
            function PagePlus(){
                if(<?php echo $page ?>+1 <= <?php echo $brojstr; ?>){
                    window.location="webshop.php?page=<?php echo $page+1; ?>"
                }
            }
        </script>
        <form action="" method="post">
            <input type="button" value="<<" onclick="PageMinus()">
            <input type="button" value="<?php if($page-1<=0){echo '...';}
            else echo $page-1; ?>" name="strminus" onclick="PageMinus()">
            <input type="button" value="<?php echo $page ?>" name="trenstr">
            <input type="button" value="<?php if($page+1>$brojstr){echo '...';}
            else echo $page+1 ?>" name="strplus" onclick="PagePlus()">
            <input type="button" value=">>" onclick="PagePlus()">
        </form>
    </div>
    <div class="okvirgrid">
        <?php
        $x=1;
        if(mysqli_num_rows($stmt) != 0){
            foreach ($stmt as $row) {
                $Ime = $row['Ime'];
                $Cijena = $row['Cijena'];
                $Opis = $row['Opis'];
                $Slika = $row['Slika'];
                echo
                    '
                    <div class="item' . $x . '">
                            <div class="item">
                                <a href="product.php?prod=' . $Ime . '">
                                    <img src="slike/' . $Slika . '" alt="' . $Ime . '.">
                                </a>
                                <a href="product.php?prod=' . $Ime . '">
                                    <h2>' . $Ime . '</h2>
                                    <h2>' . $Cijena . ' kn</h2>
                                    <p>' . $Opis . '</p>
                                </a>
                            </div>
                    </div>
                   ';
                $x++;
            }
        }
        else {
            echo "Nema rezultata...";
        }
        ?>
    </div>
</div>
<div class="spacer" style="background-image: url('slike/Website Images/webshopfooter.svg')"></div>
<footer class="footer">
<div style="text-align: center"><?php echo $query; echo '<br>'; echo 'maxstranice: '; echo $brojstr; echo '<br>Filters:'; echo $_SESSION["filters"]?></div>
    <a style="float: right; font: 25px 'Arimo', sans-serif" href="adminlogin.php">Admin</a>
</footer>
<?php
CloseCon($conn);
?>
</body>
</html>