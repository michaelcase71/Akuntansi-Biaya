<div class="modal fade" id="insertModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Form Tambah Pendapatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="webservice/insert.php" enctype="multipart/form-data">
                    <!-- Informasi Umum -->

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Platform</label>
                        <select data-trigger class="form-select" name="nama_platform" id="nama" required>
                            <option selected disabled>Masukkan Nama</option>
                            <?php
                            $queryGetNama = "SELECT * FROM master_platform WHERE status = 'Aktif'";
                            $getNama = mysqli_query($koneksi, $queryGetNama);
                            while ($nama = mysqli_fetch_assoc($getNama)) {
                                echo "<option value='{$nama['id_platform']}'>{$nama['nama_platform']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="totalPendapatan" class="form-label">Total Pendapatan</label>
                        <input type="text" class="form-control" name="total_pendapatan" id="totalPendapatan" required>
                        <input type="hidden" name="total_pendapatan_clean" id="totalPendapatanClean">
                    </div>

                    <div class="mb-3">
                        <label for="" class="form-label">Tanggal Pendapatan</label>
                        <input type="date" class="form-control" name="tanggal_pendapatan" placeholder="Masukkan tanggal terakhir pada bulan yang dipilih" id="" required>
                    </div>

                    <div class="mb-3" style="display: none;">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="idstatus">
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>


                    <!-- Tambah Barang -->
                    <label class="form-label"><strong>Detail Barang Terjual</strong></label>
                    <div id="form-barang">
                        <div class="row mb-3 barang-item">
                            <div class="col-md-3">
                                <select class="form-control " name="nama_barang[]" required>
                                    <option selected disabled>Pilih Material</option>
                                    <?php
                                    $queryGetNama = "SELECT * FROM master_barang_jadi WHERE status = 'Aktif'";
                                    $getNama = mysqli_query($koneksi, $queryGetNama);
                                    while ($nama = mysqli_fetch_assoc($getNama)) {
                                        echo "<option value='{$nama['id_barang_jadi']}'>{$nama['nama_barang']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control total-barang" name="total_barang[]" placeholder="total" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-barang">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="tambah-barang" class="btn btn-success btn-sm mb-3">+ Tambah</button>

                    <!-- Tombol Simpan -->
                    <div class="mb-3 d-flex justify-content-end">
                        <button name="insert_pendapatan" type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const totalPendapatanInput = document.getElementById('totalPendapatan');
    const totalPendapatanClean = document.getElementById('totalPendapatanClean');

    totalPendapatanInput.addEventListener('input', function(e) {
        // Ambil nilai input tanpa format
        let value = e.target.value.replace(/[^0-9]/g, ''); // Hapus karakter non-angka

        // Cegah nilai kosong
        if (value === '') {
            totalPendapatanInput.value = '';
            totalPendapatanClean.value = '';
            return;
        }

        // Format nilai sebagai Rupiah
        const formattedValue = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);

        // Perbarui input dengan format Rupiah
        totalPendapatanInput.value = formattedValue;

        // Simpan nilai bersih (tanpa format) ke input tersembunyi
        totalPendapatanClean.value = value;
    });

    // Tambah Baris Barang
    document.getElementById('tambah-barang').addEventListener('click', function() {
        const formBarang = document.getElementById('form-barang');
        const newBarang = document.createElement('div');
        newBarang.classList.add('row', 'mb-3', 'barang-item');
        newBarang.innerHTML = `
            <div class="col-md-3">
                <select class="form-control " name="nama_barang[]" required>
                    <option selected disabled>Pilih Material</option>
                    <?php
                    $queryGetNama = "SELECT * FROM master_barang_jadi WHERE status = 'Aktif'";
                    $getNama = mysqli_query($koneksi, $queryGetNama);
                    while ($nama = mysqli_fetch_assoc($getNama)) {
                        echo "<option value='{$nama['id_barang_jadi']}'>{$nama['nama_barang']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control total-barang" name="total_barang[]" placeholder="total" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm remove-barang">X</button>
            </div>
        `;
        formBarang.appendChild(newBarang);
        newBarang.querySelector('.remove-barang').addEventListener('click', () => newBarang.remove());
    });
</script>