<?php
// ===== KONFIGURASI FILE =====
$jadwalFile = "jadwal.json";
$tugasFile  = "tugas.json";

function loadData($file){
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}
function saveData($file,$data){
    file_put_contents($file,json_encode($data,JSON_PRETTY_PRINT));
}

$jadwal = loadData($jadwalFile);
$tugas  = loadData($tugasFile);

// ===== PROSES INPUT =====
if(isset($_POST['add_jadwal'])){
    $jadwal[] = [
        "mata"    => htmlspecialchars($_POST['mata']),
        "hari"    => htmlspecialchars($_POST['hari']),
        "jam"     => htmlspecialchars($_POST['jam']),
        "ruangan" => htmlspecialchars($_POST['ruangan']),
        "dosen"   => htmlspecialchars($_POST['dosen']),
        "sks"     => (int)$_POST['sks']
    ];
    saveData($jadwalFile,$jadwal);
    header("Location: ".$_SERVER['PHP_SELF']); exit;
}

if(isset($_POST['add_tugas'])){
    $tugas[] = [
        "nama"    => htmlspecialchars($_POST['nama']),
        "matkul"  => htmlspecialchars($_POST['matkul']),
        "deadline"=> htmlspecialchars($_POST['deadline']),
        "status"  => "Belum"
    ];
    saveData($tugasFile,$tugas);
    header("Location: ".$_SERVER['PHP_SELF']); exit;
}

if(isset($_GET['done'])){
    $i = (int)$_GET['done'];
    if(isset($tugas[$i])){
        $tugas[$i]['status'] = "Selesai";
        saveData($tugasFile,$tugas);
    }
    header("Location: ".$_SERVER['PHP_SELF']); exit;
}

// ===== FUNGSI PENDUKUNG =====
function totalSKS($jadwal){
    return array_sum(array_column($jadwal,'sks'));
}
function jadwalHariIni($jadwal){
    $hari = date("l");
    return array_filter($jadwal,function($j) use($hari){
        return strtolower($j['hari'])==strtolower($hari);
    });
}
function tugasPending($tugas){
    return array_filter($tugas,function($t){ return $t['status']=="Belum"; });
}
function tugasMendesak($tugas){
    $alert=[];
    foreach($tugas as $t){
        if($t['status']=="Belum"){
            $d = strtotime($t['deadline']);
            if($d>=time() && $d - time() <= 3*24*60*60){
                $alert[]=$t;
            }
        }
    }
    return $alert;
}

$today   = jadwalHariIni($jadwal);
$pending = tugasPending($tugas);
$alert   = tugasMendesak($tugas);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Agenda Digital Mahasiswa</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background: linear-gradient(135deg, #e0e7ff 0%, #f4f6f9 100%);
  font-family: 'Segoe UI',sans-serif;
  min-height: 100vh;
}
.navbar {
  border-radius: 0 0 18px 18px;
  box-shadow: 0 4px 16px rgba(13,110,253,0.08);
}
.hero-header {
  background: linear-gradient(90deg, #0d6efd 60%, #6c63ff 100%);
  color: #fff;
  border-radius: 18px;
  padding: 2.5rem 2rem 2rem 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 6px 24px rgba(13,110,253,0.10);
  text-align: center;
  position: relative;
  overflow: hidden;
}
.hero-header h1 {
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 0.5rem;
}
.hero-header p {
  font-size: 1.2rem;
  opacity: 0.95;
}
.card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.06);
  margin-bottom: 2rem;
  transition: box-shadow 0.2s;
  background: #fff;
}
.card:hover {
  box-shadow: 0 8px 32px rgba(13,110,253,0.10);
}
.section-title {
  border-left: 4px solid #0d6efd;
  padding-left: 12px;
  margin-bottom: 1.2rem;
  font-weight: 700;
  color: #222;
  font-size: 1.2rem;
  letter-spacing: 0.5px;
}
.table {
  border-radius: 12px;
  overflow: hidden;
}
.table-striped>tbody>tr:nth-of-type(odd) {
  background-color: #f8fafc;
}
.table-hover tbody tr:hover {
  background-color: #e7f1ff;
  transition: background 0.2s;
}
.btn-primary, .btn-success {
  border-radius: 8px;
  font-weight: 500;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 8px rgba(13,110,253,0.07);
  transition: background 0.18s, box-shadow 0.18s;
}
.btn-primary:hover, .btn-success:hover {
  box-shadow: 0 4px 16px rgba(13,110,253,0.14);
  transform: translateY(-2px) scale(1.03);
}
.done-btn {
  padding: 5px 14px;
  border-radius: 8px;
}
input.form-control, select.form-select {
  border-radius: 8px;
  border: 1.5px solid #dbeafe;
  transition: border 0.18s;
}
input.form-control:focus, select.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 2px #e0e7ff;
}
::-webkit-input-placeholder { color: #b6b6b6; }
::-moz-placeholder { color: #b6b6b6; }
:-ms-input-placeholder { color: #b6b6b6; }
::placeholder { color: #b6b6b6; }
.icon-circle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 38px; height: 38px;
  border-radius: 50%;
  background: #e0e7ff;
  color: #0d6efd;
  font-size: 1.3rem;
  margin-right: 10px;
}
@media (max-width: 600px) {
  .hero-header { padding: 1.2rem 0.5rem 1.2rem 0.5rem; }
  .section-title { font-size: 1rem; }
}
footer {
  margin-top: 2rem;
  text-align: center;
  color: #888;
  font-size: 0.98rem;
  padding-bottom: 1.5rem;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">Agenda Mahasiswa</a>
  </div>
</nav>

<div class="container">

  <!-- HERO HEADER -->
  <div class="hero-header mb-4">
    <h1>Agenda Digital Mahasiswa</h1>
    <p>Kelola jadwal kuliah & tugasmu dengan mudah dan modern.</p>
  </div>

  <!-- DASHBOARD -->
  <div class="card p-4">
    <h5 class="section-title"><span class="icon-circle"><i class="bi bi-speedometer2"></i></span>Dashboard</h5>
    <div class="row mb-3">
      <div class="col-md-4 mb-2">
        <div class="p-3 rounded bg-primary text-white shadow-sm d-flex align-items-center">
          <span class="icon-circle bg-white text-primary me-2"><i class="bi bi-book"></i></span>
          <div>
            <div style="font-size:1.2rem;font-weight:600"><?= totalSKS($jadwal) ?></div>
            <div style="font-size:0.98rem;">Total SKS</div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-2">
        <div class="p-3 rounded bg-warning text-dark shadow-sm d-flex align-items-center">
          <span class="icon-circle bg-white text-warning me-2"><i class="bi bi-calendar-event"></i></span>
          <div>
            <div style="font-size:1.2rem;font-weight:600"><?= count($today) ?></div>
            <div style="font-size:0.98rem;">Jadwal Hari Ini</div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-2">
        <div class="p-3 rounded bg-danger text-white shadow-sm d-flex align-items-center">
          <span class="icon-circle bg-white text-danger me-2"><i class="bi bi-exclamation-triangle"></i></span>
          <div>
            <div style="font-size:1.2rem;font-weight:600"><?= count($alert) ?></div>
            <div style="font-size:0.98rem;">Tugas Mendesak</div>
          </div>
        </div>
      </div>
    </div>

    <h6>Jadwal Hari Ini (<?= date("l") ?>):</h6>
    <ul>
      <?php if(empty($today)): ?>
        <li class="text-muted">Tidak ada jadwal hari ini</li>
      <?php else: foreach($today as $j): ?>
        <li><strong><?= $j['mata'] ?></strong> - <?= $j['jam'] ?> @ <?= $j['ruangan'] ?> (<?= $j['dosen'] ?>)</li>
      <?php endforeach; endif; ?>
    </ul>

    <h6 class="mt-3">Tugas Belum Selesai:</h6>
    <ul>
      <?php if(empty($pending)): ?>
        <li class="text-muted">Tidak ada tugas pending</li>
      <?php else: foreach($pending as $t): ?>
        <li><?= $t['nama'] ?> (<?= $t['matkul'] ?>) - Deadline: <?= $t['deadline'] ?></li>
      <?php endforeach; endif; ?>
    </ul>

    <h6 class="mt-3 text-danger">⚠️ Tugas Mendekati Deadline (≤3 hari):</h6>
    <ul>
      <?php if(empty($alert)): ?>
        <li class="text-muted">Tidak ada tugas mendesak</li>
      <?php else: foreach($alert as $a): ?>
        <li class="fw-bold text-danger"><?= $a['nama'] ?> (<?= $a['matkul'] ?>) - Deadline: <?= $a['deadline'] ?></li>
      <?php endforeach; endif; ?>
    </ul>
  </div>

  <!-- FORM JADWAL -->
  <div class="card p-4">
    <h5 class="section-title">Tambah Jadwal Kuliah</h5>
    <form method="post" class="row g-3">
      <div class="col-md-6"><input class="form-control" name="mata" placeholder="Mata Kuliah" required></div>
      <div class="col-md-6">
        <select class="form-select" name="hari" required>
          <option value="">--Pilih Hari--</option>
          <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $h) echo "<option>$h</option>"; ?>
        </select>
      </div>
      <div class="col-md-4"><input class="form-control" name="jam" placeholder="08:00-10:00" required></div>
      <div class="col-md-4"><input class="form-control" name="ruangan" placeholder="Ruangan" required></div>
      <div class="col-md-4"><input class="form-control" name="dosen" placeholder="Dosen Pengampu" required></div>
      <div class="col-md-3"><input class="form-control" type="number" name="sks" placeholder="SKS" min="1" max="6" required></div>
      <div class="col-12"><button class="btn btn-primary" type="submit" name="add_jadwal">Tambah Jadwal</button></div>
    </form>

    <div class="table-responsive mt-4">
      <table class="table table-striped">
        <thead class="table-primary">
          <tr><th>Mata Kuliah</th><th>Hari</th><th>Jam</th><th>Ruangan</th><th>Dosen</th><th>SKS</th></tr>
        </thead>
        <tbody>
          <?php if($jadwal): foreach($jadwal as $j): ?>
            <tr>
              <td><?= $j['mata'] ?></td><td><?= $j['hari'] ?></td>
              <td><?= $j['jam'] ?></td><td><?= $j['ruangan'] ?></td>
              <td><?= $j['dosen'] ?></td><td><?= $j['sks'] ?></td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center text-muted">Belum ada jadwal</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- FORM TUGAS -->
  <div class="card p-4">
    <h5 class="section-title">Tambah Tugas</h5>
    <form method="post" class="row g-3">
      <div class="col-md-4"><input class="form-control" name="nama" placeholder="Nama Tugas" required></div>
      <div class="col-md-4"><input class="form-control" name="matkul" placeholder="Mata Kuliah" required></div>
      <div class="col-md-4"><input class="form-control" type="date" name="deadline" required></div>
      <div class="col-12"><button class="btn btn-primary" type="submit" name="add_tugas">Tambah Tugas</button></div>
    </form>

    <div class="table-responsive mt-4">
      <table class="table table-striped">
        <thead class="table-primary">
          <tr><th>Nama</th><th>Mata Kuliah</th><th>Deadline</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          <?php if($tugas): foreach($tugas as $i=>$t): ?>
            <tr>
              <td><?= $t['nama'] ?></td>
              <td><?= $t['matkul'] ?></td>
              <td><?= $t['deadline'] ?></td>
              <td><?= $t['status'] ?></td>
              <td>
                <?php if($t['status']=="Belum"): ?>
                  <a href="?done=<?= $i ?>" class="btn btn-success btn-sm done-btn">Selesai</a>
                <?php else: ?>- <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center text-muted">Belum ada tugas</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Tambahkan Bootstrap Icons CDN sebelum </body> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.js"></script>
<footer>
  &copy; <?= date('Y') ?> Agenda Mahasiswa &mdash; Made with <span style="color:#e25555;">&#10084;</span>
</footer>
</body>
</html>
