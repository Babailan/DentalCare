<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);

        $serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
        if ($name === '' || $price === false || $price < 0 || ($action === 'update' && !$serviceId)) {
            $message = 'Enter a service name and a valid, non-negative price.';
            $messageType = 'error';
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $conn->prepare('INSERT INTO Services (Name, Description, Price) VALUES (?, ?, ?)');
                    $stmt->execute([$name, $description, $price]);
                    $message = 'Service added.';
                } else {
                    $stmt = $conn->prepare('UPDATE Services SET Name = ?, Description = ?, Price = ? WHERE ServiceID = ?');
                    $stmt->execute([$name, $description, $price, $serviceId]);
                    $message = 'Service details and price updated.';
                }
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'UNIQUE')) {
                    $message = 'A service with that name already exists.';
                    $messageType = 'error';
                } else {
                    throw $e;
                }
            }
        }
    } elseif ($action === 'toggle') {
        $serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
        $active = filter_var($_POST['active'] ?? null, FILTER_VALIDATE_INT);
        if ($serviceId && in_array($active, [0, 1], true)) {
            $stmt = $conn->prepare('UPDATE Services SET IsActive = ? WHERE ServiceID = ?');
            $stmt->execute([$active, $serviceId]);
            $message = 'Service availability updated.';
        } else {
            $message = 'Invalid service selection.';
            $messageType = 'error';
        }
    }
}

$services = $conn->query('SELECT ServiceID, Name, Description, Price, IsActive FROM Services ORDER BY IsActive DESC, Name ASC')->fetchAll();
include __DIR__ . '/../header.php';
?>

<div class="mx-auto w-full max-w-5xl px-4 py-8">
    <h1 class="mb-6 text-3xl font-bold">Appointment services &amp; prices</h1>
    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo $messageType; ?> mb-6" role="status"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <section class="card card-border mb-8 bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title">Add a service</h2>
            <form method="post" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <input type="hidden" name="action" value="create">
                <label>
                    <span class="mb-1 block text-sm font-medium">Service name</span>
                    <input class="input w-full" name="name" maxlength="120" required>
                </label>
                <label>
                    <span class="mb-1 block text-sm font-medium">Price (RM)</span>
                    <input class="input w-full" type="number" name="price" min="0" step="0.01" required>
                </label>
                <label class="md:col-span-2">
                    <span class="mb-1 block text-sm font-medium">Description (optional)</span>
                    <textarea class="textarea w-full" name="description" rows="2" maxlength="500"></textarea>
                </label>
                <div class="md:col-span-2">
                    <button class="btn btn-primary" type="submit">Add service</button>
                </div>
            </form>
        </div>
    </section>

    <div class="overflow-x-auto">
        <table class="table table-zebra">
            <thead><tr><th>Service</th><th>Description</th><th>Price (RM)</th><th>Availability</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <?php $formId = 'service-' . (int) $service['ServiceID']; ?>
                        <td><input class="input input-sm w-44" form="<?php echo $formId; ?>" name="name" maxlength="120" value="<?php echo htmlspecialchars($service['Name']); ?>" required></td>
                        <td><input class="input input-sm w-64" form="<?php echo $formId; ?>" name="description" maxlength="500" value="<?php echo htmlspecialchars($service['Description']); ?>"></td>
                        <td><input class="input input-sm w-28" form="<?php echo $formId; ?>" type="number" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars((string) $service['Price']); ?>" required></td>
                        <td><?php echo (int) $service['IsActive'] === 1 ? 'Active' : 'Inactive'; ?></td>
                        <td>
                            <form id="<?php echo $formId; ?>" method="post">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="service_id" value="<?php echo (int) $service['ServiceID']; ?>">
                                <button class="btn btn-primary btn-sm" type="submit">Save</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="service_id" value="<?php echo (int) $service['ServiceID']; ?>">
                                <input type="hidden" name="active" value="<?php echo (int) !(bool) $service['IsActive']; ?>">
                                <button class="btn btn-sm <?php echo (int) $service['IsActive'] === 1 ? 'btn-warning' : 'btn-success'; ?>" type="submit">
                                    <?php echo (int) $service['IsActive'] === 1 ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$services): ?><tr><td colspan="5">No services have been added yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
