<?php
// Initialize the XML file path
$xmlFilePath = './db/employees.xml';
if (!file_exists($xmlFilePath)) {
    $dom = new DOMDocument('1.0', 'UTF-8');
    $root = $dom->createElement('employees');
    $dom->appendChild($root);
    $dom->save($xmlFilePath);
} else {
    $dom = new DOMDocument();
    $dom->load($xmlFilePath);
}

function insertEmployee($name, $phones, $address, $email)
{
    global $dom, $xmlFilePath;
    $employee = $dom->createElement('employee');
    $employee->appendChild($dom->createElement('name', $name));
    $phonesElement = $dom->createElement('phones');
    foreach ($phones as $type => $number) {
        $phone = $dom->createElement('phone', $number);
        $phone->setAttribute('type', $type);
        $phonesElement->appendChild($phone);
    }
    $employee->appendChild($phonesElement);
    $addressElement = $dom->createElement('addresses');
    $addressNode = $dom->createElement('address');
    foreach ($address as $key => $value) {
        $addressNode->appendChild($dom->createElement($key, $value));
    }
    $addressElement->appendChild($addressNode);
    $employee->appendChild($addressElement);
    $employee->appendChild($dom->createElement('email', $email));
    $dom->documentElement->appendChild($employee);
    $dom->save($xmlFilePath);
}

function updateEmployee($oldName, $newName, $newPhones, $newAddress, $newEmail)
{
    global $dom, $xmlFilePath;
    $employees = $dom->getElementsByTagName('employee');
    foreach ($employees as $employee) {
        $nameNode = $employee->getElementsByTagName('name')->item(0);
        if ($nameNode->nodeValue == $oldName) {
            $nameNode->nodeValue = $newName;
            // Update phones
            $phonesNode = $employee->getElementsByTagName('phones')->item(0);
            $phonesNode->parentNode->removeChild($phonesNode);
            $newPhonesElement = $dom->createElement('phones');
            foreach ($newPhones as $type => $number) {
                $phone = $dom->createElement('phone', $number);
                $phone->setAttribute('type', $type);
                $newPhonesElement->appendChild($phone);
            }
            $employee->appendChild($newPhonesElement);
            // Update address
            $addressNode = $employee->getElementsByTagName('address')->item(0);
            foreach ($newAddress as $key => $value) {
                $addressNode->getElementsByTagName($key)->item(0)->nodeValue = $value;
            }
            // Update email
            $employee->getElementsByTagName('email')->item(0)->nodeValue = $newEmail;
            $dom->save($xmlFilePath);
            return;
        }
    }
}

function deleteEmployee($name)
{
    global $dom, $xmlFilePath;
    $employees = $dom->getElementsByTagName('employee');
    foreach ($employees as $employee) {
        $nameNode = $employee->getElementsByTagName('name')->item(0);
        if ($nameNode->nodeValue == $name) {
            $dom->documentElement->removeChild($employee);
            $dom->save($xmlFilePath);
            return;
        }
    }
}

function searchEmployees($searchValue, $searchField)
{
    global $dom;
    $searchResults = [];
    $employees = $dom->getElementsByTagName('employee');
    foreach ($employees as $employee) {
        $currentFieldValue = $employee->getElementsByTagName($searchField)->item(0)->nodeValue;
        if (str_contains(strtolower($currentFieldValue), strtolower($searchValue))) {
            array_push($searchResults, $employee);
        }
    }
    return $searchResults;
}

$searchResults = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $phones = $_POST['phones'] ?? [];
    $address = $_POST['address'] ?? [];
    $email = trim($_POST['email'] ?? '');
    $searchValue = trim($_POST['searchValue'] ?? '');
    $searchField = $_POST['searchField'] ?? 'name';

    switch ($action) {
        case 'Insert':
            insertEmployee($name, $phones, $address, $email);
            break;
        case 'Update':
            updateEmployee($name, $name, $phones, $address, $email);
            break;
        case 'Delete':
            deleteEmployee($name);
            break;
        case 'Search':
            $searchResults = searchEmployees($searchValue, $searchField);
            break;
    }
}

function displaySearchResults($searchResults)
{
    if (!empty($searchResults)) {
        echo '<div class="search-results mt-4">';
        echo '<h2 class="mb-3">Search Results</h2>';
        foreach ($searchResults as $employee) {
            echo '<div class="card mb-3">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($employee->getElementsByTagName('name')->item(0)->nodeValue) . '</h5>';
            echo '<p class="card-text"><strong>Phone:</strong> ' . htmlspecialchars($employee->getElementsByTagName('phones')->item(0)->nodeValue) . '</p>';
            echo '<p class="card-text"><strong>Address:</strong> ' . htmlspecialchars($employee->getElementsByTagName('address')->item(0)->nodeValue) . '</p>';
            echo '<p class="card-text"><strong>Email:</strong> ' . htmlspecialchars($employee->getElementsByTagName('email')->item(0)->nodeValue) . '</p>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f1f4f8;
        }

        .container {
            background-color: #ffffff;
            padding-top: 30px;
            padding-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 50px;
        }

        .form-control,
        .btn,
        .input-group-text,
        .form-select {
            border-radius: 0.5rem;
        }

        .btn {
            padding: 10px 24px;
        }

        .form-title {
            color: #333;
            margin-bottom: 30px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center form-title">Employee Manager</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="index.php" method="post" class="p-4">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Name">
                    </div>
                    <div class="mb-3">
                        <label for="phones" class="form-label">Phones</label>
                        <input type="text" class="form-control" id="phones" name="phones[mobile]" placeholder="Mobile">
                        <input type="text" class="form-control" id="phones" name="phones[work]" placeholder="Work">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="street" name="address[street]" placeholder="Street">
                        <input type="text" class="form-control" id="building_number" name="address[building_number]" placeholder="Building Number">
                        <input type="text" class="form-control" id="region" name="address[region]" placeholder="Region">
                        <input type="text" class="form-control" id="city" name="address[city]" placeholder="City">
                        <input type="text" class="form-control" id="country" name="address[country]" placeholder="Country">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                    </div>
                    <div class="text-center mb-3">
                        <button type="submit" name="action" value="Insert" class="btn btn-primary me-2">Insert</button>
                        <button type="submit" name="action" value="Update" class="btn btn-secondary me-2">Update</button>
                        <button type="submit" name="action" value="Delete" class="btn btn-danger me-2">Delete</button>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search Value" name="searchValue" aria-label="Search Value">
                            <select class="form-select" name="searchField">
                                <option value="name">Name</option>
                                <option value="phones">Phone</option>
                                <option value="address">Address</option>
                                <option value="email">Email</option>
                            </select>
                            <button class="btn btn-outline-secondary" type="submit" name="action" value="Search">Search</button>
                        </div>
                    </div>
                </form>
                <?php
                if (!empty($searchResults)) {
                    displaySearchResults($searchResults);
                }
                ?>
            </div>
        </div>
    </div>
    <!-- Display all employees in a list -->
    <div class="container mt-5">
        <h2 class="text-center">All Employees</h2>
        <ul class="list-group">
            <?php
            $employees = $dom->getElementsByTagName('employee');
            foreach ($employees as $employee) {
                echo '<li class="list-group-item">' . htmlspecialchars($employee->getElementsByTagName('name')->item(0)->nodeValue) . '</li>';
            }
            ?>
        </ul>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>