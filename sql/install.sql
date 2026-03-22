CREATE TABLE IF NOT EXISTS `mc_plug_featured` (
    `id_featured` int(11) NOT NULL AUTO_INCREMENT,
    `id_product` int(11) NOT NULL,
    `position` int(11) DEFAULT 0,
    PRIMARY KEY (`id_featured`),
    UNIQUE KEY `id_product` (`id_product`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;