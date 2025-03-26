<div class="modal fade" id="insertModalKeluar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Form Data Penggunaan Bahan Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="webservice/insert.php" enctype="multipart/form-data">
                    <!-- Informasi Umum -->

                    <div class="mb-3">
                        <label for="nama_pekerja" class="form-label">Nama Pekerja</label>
                        <select class="form-select" name="nama_pekerja" required>
                            <option selected disabled>Pilih Nama</option>
                            <?php
                            $queryGetNama = "SELECT * FROM master_pekerja WHERE status = 'Aktif'";
                            $getNama = mysqli_query($koneksi, $queryGetNama);
                            while ($nama = mysqli_fetch_assoc($getNama)) {
                            ?>
                                <option value="<?= $nama['id_pekerja'] ?>"><?= $nama['nama_pekerja'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Barang Jadi yang Akan Dibuat</label>
                        <select data-trigger class="form-select" name="nama_barang" id="namabarang" required>
                            <option selected disabled>Masukkan Nama</option>
                            <?php
                            $queryGetNama = "SELECT * FROM master_barang_jadi WHERE status = 'Aktif'";
                            $getNama = mysqli_query($koneksi, $queryGetNama);
                            while ($nama = mysqli_fetch_assoc($getNama)) {
                            ?>
                                <option value="<?= $nama['id_barang_jadi'] ?>">
                                    <?= $nama['nama_barang'] ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3" style="display: none;">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="idstatus">
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="tanggal_pengambilan" class="form-label">Tanggal Pengambilan</label>
                            <input type="date" class="form-control" name="tanggal_pengambilan" id="tanggal_pengambilan" required>
                        </div>
                        <div class="col-md-3">
                            <label for="target_jumlah" class="form-label">Estimasi Hari Pengerjaan</label>
                            <input type="number" class="form-control" name="target_jumlah" id="target_jumlah" required>
                        </div>
                        <div class="col-md-3">
                            <label for="estimasi_tanggal_jadi" class="form-label">Estimasi Tanggal Jadi</label>
                            <input type="date" class="form-control" name="estimasi_tanggal_jadi" id="estimasi_tanggal_jadi" required readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="target_jumlah" class="form-label">Target Jumlah Jadi</label>
                            <input type="number" class="form-control" name="target_jumlah" required>
                        </div>
                    </div>


                    <hr>
                    <!-- Tambah Barang -->
                    <label class="form-label"><strong>Detail pengambilan</strong></label>
                    <div id="form-barang-keluar">
                        <div class="row mb-3 barang-item">
                            <div class="col-md-3">
                                <select class="form-control jumlah-barang" name="nama_bahan_material[]" required>
                                    <option selected disabled>Pilih Material</option>
                                    <?php
                                    $queryGetNama = "SELECT * FROM master_bahan_material WHERE status = 'Aktif'";
                                    $getNama = mysqli_query($koneksi, $queryGetNama);
                                    while ($nama = mysqli_fetch_assoc($getNama)) {
                                    ?>
                                        <option value="<?= $nama['id_bahan_material'] ?>">
                                            <?= $nama['nama_bahan_material'] ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control jumlah-barang" name="jumlah_barang[]" placeholder="Jumlah" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm hapus-bahan-keluar">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="tambah-barang-pengeluaran" class="btn btn-success btn-sm mb-3">+ Tambah</button>
                    <hr>
                    <!-- Tombol Simpan -->
                    <div class="mb-3 d-flex justify-content-end">
                        <button name="insert_stokkeluar" type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function hitungSubtotal(row) {
        const jumlahInput = row.querySelector('[name="jumlah_barang[]"]');
        const hargaInput = row.querySelector('[name="harga_barang[]"]');
        const subtotalInput = row.querySelector('[name="subtotal[]"]');

        const jumlah = parseFloat(jumlahInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        subtotalInput.value = jumlah * harga;

        hitungTotalKeseluruhan();
    }

    function hitungTotalKeseluruhan() {
        const subtotalInputs = document.querySelectorAll('[name="subtotal[]"]');
        let total = 0;
        subtotalInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total-pengambilan').value = total;
    }

    // Tambah Baris Barang
    document.getElementById('tambah-barang-pengeluaran').addEventListener('click', function() {
        const formBahanKeluar = document.getElementById('form-barang-keluar');
        const newBarang = document.createElement('div');
        newBarang.classList.add('row', 'mb-3', 'barang-item');
        newBarang.innerHTML = `
        <div class="col-md-3">
            <select class="form-control" name="nama_bahan_material[]" required>
                <option selected disabled>Pilih Material</option>
                <?php
                $queryGetNama = "SELECT * FROM master_bahan_material WHERE status = 'Aktif'";
                $getNama = mysqli_query($koneksi, $queryGetNama);
                while ($nama = mysqli_fetch_assoc($getNama)) {
                ?>
                    <option value="<?= $nama['id_bahan_material'] ?>">
                        <?= $nama['nama_bahan_material'] ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control" name="jumlah_barang[]" placeholder="Jumlah" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm hapus-bahan-keluar">X</button>
        </div>
    `;
        formBahanKeluar.appendChild(newBarang);
        attachEventsToRow(newBarang);
    });

    function attachEventsToRow(row) {
        row.querySelector('.hapus-bahan-keluar').addEventListener('click', () => {
            row.remove();
            hitungTotalKeseluruhan();
        });
    }

    document.getElementById('tanggal_pengambilan').addEventListener('input', updateEstimasiTanggalJadi);
    document.getElementById('target_jumlah').addEventListener('input', updateEstimasiTanggalJadi);

    function updateEstimasiTanggalJadi() {
        const tanggalPengambilan = document.getElementById('tanggal_pengambilan').value;
        const targetJumlah = document.getElementById('target_jumlah').value;

        if (tanggalPengambilan && targetJumlah) {
            const tanggalPengambilanDate = new Date(tanggalPengambilan);
            // Menambahkan jumlah hari ke tanggal pengambilan
            tanggalPengambilanDate.setDate(tanggalPengambilanDate.getDate() + parseInt(targetJumlah));

            // Mengatur nilai estimasi_tanggal_jadi
            const estimasiTanggalJadi = document.getElementById('estimasi_tanggal_jadi');
            estimasiTanggalJadi.value = formatDate(tanggalPengambilanDate);
        }
    }

    // Fungsi untuk format tanggal menjadi format YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }


    // Pasang Event Listener Awal
    document.querySelectorAll('.barang-item').forEach(attachEventsToRow);
</script>