<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تبرعات الطلب #<?= $request_id ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="container py-4">

  <a href="profile.php" class="btn btn-secondary mb-3">🔙 رجوع للملف الشخصي</a>

  <h3>💉 التبرعات لطلب مستشفى: <?= htmlspecialchars($request['hospital_name']) ?> - <?= htmlspecialchars($request['city']) ?></h3>

  <?php if (empty($donations)): ?>
    <p>🚫 لا توجد تبرعات مسجلة لهذا الطلب بعد.</p>
  <?php else: ?>
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>المتبرع</th>
          <th>رقم الهاتف</th>
          <th>تاريخ التبرع</th>
          <th>الحالة</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($donations as $don): ?>
<tr>
  <td><?= htmlspecialchars($don['name']) ?></td>
  <td><?= htmlspecialchars($don['phone']) ?></td>
  <td><?= date('d-m-Y', strtotime($don['donated_at'])) ?></td>
  <td>
    <?php if ($don['status'] == 'completed'): ?>
      <span class="text-success">تم التبرع</span>
    <?php else: ?>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="confirm_donation_id" value="<?= $don['id'] ?>">
        <button type="submit" class="btn btn-sm btn-outline-success">تأكيد التبرع</button>
      </form>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

</body>
</html>