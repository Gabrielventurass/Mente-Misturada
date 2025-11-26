<header>
    <div>
        <?php if(!isset($h2))
                echo "<h2>Página não nomeada</h2>";
              else
                echo "<h1 class='text-3xl font-bold mb-6'>".$h2."</h1>";?>
    </div>
    <div>
        <a class="home" href="painel_adm.php">Home</a>
        <a class="logout" href="logout_adm.php">Sair</a>
    </div>
</header>
<style>
header {
  background: #2d3a4a;
  width: 100%;
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
header h2 {
  margin: 5px;
}
button, a.logout {
  background: #b91c1c;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 10px 15px;
  text-decoration: none;
  cursor: pointer;
  transition: 0.3s;
}
button:hover, a.logout:hover {
  background: #ef4444;
}
    button, a.home {
  background: #1c6db9ff;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 10px 15px;
  text-decoration: none;
  cursor: pointer;
  transition: 0.3s;
}
button:hover, a.home:hover {
  background: #1c6db9ff;
}
body {
  font-family: Arial;
  background: #111;
  color: white;
  margin: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
}

main {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  flex: 1;
}
</style>