<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/ProjectTa/webservice/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/ProjectTa/lib/function.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/ProjectTa/pages/cetakhpp.php";

// Ambil bulan dan tahun dari form
$selectedMonth = isset($_POST['month']) ? $_POST['month'] : '';
$selectedYear = isset($_POST['year']) ? $_POST['year'] : '';

// Ambil data dari fungsi
$data = Tampil_Data("hpp");

// Filter data berdasarkan bulan dan tahun
if ($selectedMonth && $selectedYear) {
    $filteredData = array_filter($data, function ($item) use ($selectedMonth, $selectedYear) {
        $periode = DateTime::createFromFormat('Y-m', $item->periode);
        return $periode && $periode->format('m') === $selectedMonth && $periode->format('Y') === $selectedYear;
    });
} else {
    $filteredData = [];
}
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title font-size-18">Laporan Harga Pokok Produksi</h4>
                </div>
                <div class="card-body">
                    <form method="POST" id="filterForm">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <select name="month" class="form-select">
                                    <option value="">Pilih Bulan</option>
                                    <?php for ($m = 1; $m <= 12; $m++) { ?>
                                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $selectedMonth == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                            <?= date("F", mktime(0, 0, 0, $m, 10)) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="year" class="form-select">
                                    <option value="">Pilih Tahun</option>
                                    <?php for ($y = date("Y") - 5; $y <= date("Y"); $y++) { ?>
                                        <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary" type="submit">Filter</button>
                                <a href="" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered dt-responsive nowrap w-100 table-striped table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Nomor</th>
                                <th>Tanggal</th>
                                <th>Total Barang Jadi</th>
                                <th>Biaya Bahan Baku</th>
                                <th>Biaya Tenaga Kerja Langsung</th>
                                <th>Biaya Overhead</th>
                                <th>Total HPP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($filteredData)) {
                                $no = 1;
                                foreach ($filteredData as $j) {
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $j->periode ?></td>
                                        <td><?= $j->total_barang_jadi ?></td>
                                        <td class="text-end">Rp. <?= number_format($j->total_bahan_baku, 2, ',', '.') ?></td>
                                        <td class="text-end">Rp. <?= number_format($j->total_tenaga_kerja, 2, ',', '.') ?></td>
                                        <td class="text-end">Rp. <?= number_format($j->total_overhead, 2, ',', '.') ?></td>
                                        <td class="text-end">Rp. <?= number_format($j->total_hpp, 2, ',', '.') ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Tidak ada data untuk bulan dan tahun yang dipilih.</td></tr>";
                            } ?>
                        </tbody>
                    </table>

                    <button id="printButton" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#cetakModal" disabled>Cetak / Download PDF</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var printButton = document.getElementById("printButton");
        var filterForm = document.getElementById("filterForm");

        function checkFilterStatus() {
            if (sessionStorage.getItem("filterApplied") === "true") {
                printButton.removeAttribute("disabled");
            } else {
                printButton.setAttribute("disabled", "disabled");
            }
        }

        filterForm.addEventListener("submit", function () {
            sessionStorage.setItem("filterApplied", "true");
        });

        checkFilterStatus();
    });
</script>
