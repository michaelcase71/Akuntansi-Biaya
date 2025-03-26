<?php
include "../config.php";

// Ambil parameter periode dari GET atau gunakan bulan ini secara default
$periode = isset($_GET['periode']) ? $_GET['periode'] : date('Y-m');

// Query SQL
$sql = "WITH bahan_baku AS (
    SELECT 
        DATE_FORMAT(tpm.tanggal_pengambilan, '%Y-%m') AS periode,
        COALESCE(SUM(dpm.jumlah * (
            SELECT 
                COALESCE(SUM(dpbm.harga_satuan * dpbm.jumlah) / NULLIF(SUM(dpbm.jumlah), 0), 0)
            FROM detail_pembelian_bahan_material dpbm
            WHERE dpbm.id_bahan_material = dpm.id_bahan_material
        )), 0) AS total_bahan_material
    FROM 
        transaksi_penggunaan_bahan_material tpm
    LEFT JOIN 
        detail_penggunaan_bahan_material dpm 
        ON tpm.id_penggunaan_material = dpm.id_penggunaan_material
    WHERE 
        DATE_FORMAT(tpm.tanggal_pengambilan, '%Y-%m') = '$periode'
        AND tpm.status = 'Aktif'
    GROUP BY 
        DATE_FORMAT(tpm.tanggal_pengambilan, '%Y-%m')
),
tenaga_kerja AS (
    SELECT 
        DATE_FORMAT(tbm.tanggal, '%Y-%m') AS periode,
        COALESCE(SUM(dbdm.subtotal_upah), 0) AS total_upah
    FROM 
        transaksi_barang_jadi_masuk tbm
    LEFT JOIN 
        detail_barang_jadi_masuk dbdm 
        ON tbm.id_barang_masuk = dbdm.id_barang_masuk
    WHERE 
        DATE_FORMAT(tbm.tanggal, '%Y-%m') = '$periode'
        AND tbm.status = 'Aktif'
    GROUP BY 
        DATE_FORMAT(tbm.tanggal, '%Y-%m')
),
overhead AS (
    WITH overhead_per_bulan AS (
        SELECT
            po.tanggal AS tanggal_periode,
            DATE_ADD(po.tanggal, INTERVAL oh.bulan_ekonomis MONTH) AS akhir_periode_penyusutan,
            oh.nilai_penyusutan
        FROM 
            transaksi_pengeluaran po
        LEFT JOIN 
            detail_pengeluaran oh ON po.id_pengeluaran = oh.id_pengeluaran
        WHERE 
            po.status = 'Aktif'
    ),
    periode_biaya AS (
        SELECT
            DATE_FORMAT(DATE_ADD(opb.tanggal_periode, INTERVAL n.num MONTH), '%Y-%m') AS periode,
            opb.nilai_penyusutan
        FROM
            overhead_per_bulan opb
        JOIN (
            SELECT 0 AS num
            UNION ALL SELECT 1
            UNION ALL SELECT 2
            UNION ALL SELECT 3
            UNION ALL SELECT 4
            UNION ALL SELECT 5
            UNION ALL SELECT 6
            UNION ALL SELECT 7
            UNION ALL SELECT 8
            UNION ALL SELECT 9
            UNION ALL SELECT 10
            UNION ALL SELECT 11
        ) n ON DATE_ADD(opb.tanggal_periode, INTERVAL n.num MONTH) <= opb.akhir_periode_penyusutan
    ),
    total_biaya_overhead AS (
        SELECT
            p.periode,
            SUM(p.nilai_penyusutan) AS total_biaya
        FROM
            periode_biaya p
        GROUP BY
            p.periode
        UNION ALL
        SELECT
            DATE_FORMAT(po.tanggal, '%Y-%m') AS periode,
            COALESCE(SUM(oh.biaya_overhead), 0) AS total_biaya
        FROM 
            transaksi_pengeluaran_overhead po
        LEFT JOIN 
            detail_pengeluaran_overhead oh ON po.id_pengeluaran_overhead = oh.id_pengeluaran_overhead
        WHERE 
            DATE_FORMAT(po.tanggal, '%Y-%m') = '$periode'
            AND po.status = 'Aktif'
        GROUP BY 
            DATE_FORMAT(po.tanggal, '%Y-%m')
    )
    SELECT
        periode,
        SUM(total_biaya) AS total_biaya_overhead
    FROM
        total_biaya_overhead
    GROUP BY
        periode
),
pendapatan AS (
    SELECT 
        DATE_FORMAT(tp.tanggal_pendapatan, '%Y-%m') AS periode,
        COALESCE(SUM(tp.total_pendapatan), 0) AS total_pendapatan
    FROM 
        transaksi_pendapatan tp
    WHERE 
        DATE_FORMAT(tp.tanggal_pendapatan, '%Y-%m') = '$periode'
        AND tp.status = 'Aktif'
    GROUP BY 
        DATE_FORMAT(tp.tanggal_pendapatan, '%Y-%m')
),
hpp AS (
    SELECT 
        periode,
        'Total Pendapatan' AS keterangan,
        total_pendapatan AS total
    FROM pendapatan

    UNION ALL

    SELECT 
        periode,
        'Biaya Bahan Baku' AS keterangan,
        total_bahan_material AS total
    FROM bahan_baku

    UNION ALL

    SELECT 
        periode,
        'Biaya Tenaga Kerja' AS keterangan,
        total_upah AS total
    FROM tenaga_kerja

    UNION ALL

    SELECT 
        periode,
        'Biaya Overhead' AS keterangan,
        total_biaya_overhead AS total
    FROM overhead

    UNION ALL

    SELECT 
        periode,
        'Total HPP' AS keterangan,
        (COALESCE(total_bahan_material, 0) + COALESCE(total_upah, 0) + COALESCE(total_biaya_overhead, 0)) AS total
    FROM (
        SELECT 
            bb.periode,
            COALESCE(bb.total_bahan_material, 0) AS total_bahan_material,
            COALESCE(tk.total_upah, 0) AS total_upah,
            COALESCE(oh.total_biaya_overhead, 0) AS total_biaya_overhead
        FROM bahan_baku bb
        LEFT JOIN tenaga_kerja tk ON bb.periode = tk.periode
        LEFT JOIN overhead oh ON bb.periode = oh.periode
    ) AS combined

    UNION ALL

    SELECT 
        pp.periode,
        'Laba Rugi' AS keterangan,
        COALESCE(pp.total_pendapatan, 0) - (COALESCE(bb.total_bahan_material, 0) + COALESCE(tk.total_upah, 0) + COALESCE(oh.total_biaya_overhead, 0)) AS total
    FROM pendapatan pp
    LEFT JOIN bahan_baku bb ON pp.periode = bb.periode
    LEFT JOIN tenaga_kerja tk ON pp.periode = tk.periode
    LEFT JOIN overhead oh ON pp.periode = oh.periode
)
SELECT 
    periode,
    keterangan,
    total
FROM hpp
WHERE 
    periode = '$periode'
ORDER BY 
    FIELD(keterangan, 'Total Pendapatan', 'Biaya Bahan Baku', 'Biaya Tenaga Kerja', 'Biaya Overhead', 'Total HPP', 'Laba Rugi')

";

$hasil = mysqli_query($koneksi, $sql);

$jsonRespon = array();

if (mysqli_num_rows($hasil) > 0) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        // Menambahkan setiap keterangan ke dalam array respon
        $jsonRespon[] = $row;
    }
} else {
    // Jika tidak ada data, kirimkan data default dengan keterangan 'Laba Rugi' dan nilai 0
    $jsonRespon[] = array('periode' => $periode, 'keterangan' => 'Laba Rugi', 'total' => 0);
}

// Format respon dalam format JSON
$response = array(
    'data' => $jsonRespon,
);

// Set header JSON dan kirimkan response
header('Content-Type: application/json');
echo json_encode($response);
?>
