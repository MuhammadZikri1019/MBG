-- Script untuk membuat ulang view vw_dashboard_super_admin
-- Jalankan script ini di Wasmer phpMyAdmin

DROP TABLE IF EXISTS `vw_dashboard_super_admin`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_dashboard_super_admin` AS 
SELECT 
  (SELECT COUNT(0) FROM `tbl_pengelola_dapur` WHERE `tbl_pengelola_dapur`.`status` = 'aktif') AS `total_pengelola`, 
  (SELECT COUNT(0) FROM `tbl_dapur` WHERE `tbl_dapur`.`status` = 'aktif') AS `total_dapur`, 
  (SELECT COUNT(0) FROM `tbl_karyawan` WHERE `tbl_karyawan`.`status` = 'aktif') AS `total_karyawan`, 
  (SELECT COUNT(0) FROM `tbl_menu` WHERE `tbl_menu`.`status` = 'aktif') AS `total_menu`, 
  (SELECT COUNT(0) FROM `tbl_produksi_harian` WHERE `tbl_produksi_harian`.`tanggal_produksi` = CURDATE()) AS `produksi_hari_ini`, 
  (SELECT COUNT(0) FROM `tbl_bahan_baku` WHERE `tbl_bahan_baku`.`status` = 'tersedia') AS `total_bahan_aktif`, 
  (SELECT COALESCE(SUM(`tbl_pembelanjaan`.`total_pembelian`), 0) FROM `tbl_pembelanjaan` WHERE `tbl_pembelanjaan`.`tanggal_pembelian` = CURDATE()) AS `pembelanjaan_hari_ini`;
