<?php
// Подключение к базе данных
$host = 'localhost';
$db = 'product_catalog';
$user = 'root'; // замените на ваше имя пользователя
$pass = ''; // замените на ваш пароль

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Добавление товара
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stmt = $conn->prepare("INSERT INTO products (name, category, price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $name, $category, $price);
    $stmt->execute();
    $stmt->close();
}

// Поиск и фильтрация
$searchTerm = '';
$filterCategory = '';
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
}
if (isset($_GET['category'])) {
    $filterCategory = $_GET['category'];
}

// Получение товаров
$sql = "SELECT * FROM products WHERE name LIKE ? ";
$params = ["%$searchTerm%"];
if ($filterCategory) {
    $sql .= "AND category = ?";
    $params[] = $filterCategory;
}
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Управление каталогом товаров</h1>

    <form method="POST">
        <h2>Добавить товар</h2>
        <input type="text" name="name" placeholder="Название товара" required>
        <input type="text" name="category" placeholder="Категория" required>
        <input type="number" name="price" placeholder="Цена" step="0.01" required>
        <button type="submit" name="add_product">Добавить</button>
    </form>

    <h2>Поиск товаров</h2>
    <form method="GET">
        <input type="text" name="search" placeholder="Поиск по названию" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <select name="category">
            <option value="">Все категории</option>
            <option value="Канц-товары" <?php if ($filterCategory == 'Техника') echo 'selected'; ?>>Канц-товары</option>
            <option value="Игрушки" <?php if ($filterCategory == 'Мебель') echo 'selected'; ?>>Игрушки</option>
            <option value="Техника" <?php if ($filterCategory == 'Канц-товары') echo 'selected'; ?>>Техника</option>
            <option value="Мебель" <?php if ($filterCategory == 'Мебель') echo 'selected'; ?>>Мебель</option>
            <option value="Косметика" <?php if ($filterCategory == 'Канц-товары') echo 'selected'; ?>>Косметика</option>
        </select>
        <button type="submit">Поиск</button>
    </form>

    <h2>Каталог товаров</h2>
    <div class="catalog">
        <?php while ($product = $result->fetch_assoc()): ?>
            <div class="product">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Категория: <?php echo htmlspecialchars($product['category']); ?></p>
                <p>Цена: <?php echo htmlspecialchars($product['price']); ?>₽</p>
            </div>
        <?php endwhile; ?>
    </div>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>