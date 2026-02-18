 -- ============================================================================
-- == SQL Schema for the Warehouse Management System                         ==
-- ============================================================================
-- This script creates the necessary tables for the application to function.
-- It is designed to be run once to set up the initial database structure.
-- ============================================================================

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_level` int(1) NOT NULL COMMENT '1: Admin, 2: Manager, 3: Staff',
  `image` varchar(255) DEFAULT 'no_image.png',
  `status` int(1) NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Inactive',
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--
-- We'll add a default admin user so you can log in immediately.
-- The password is 'admin'. In a real application, this should be hashed.
--
INSERT INTO `users` (`id`, `name`, `username`, `password`, `user_level`, `status`) VALUES
(1, 'Admin User', 'admin', '$2y$10$E.g62v/1O91y.aJ.8.d.a.Kb.s34r.3s2/d.s.s5t.s.w', 1, 1);


--
-- Table structure for table `suppliers`
--
CREATE TABLE `suppliers` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Inactive',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `products`
--
CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `purchase_price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `supplier_id` int(11) UNSIGNED DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `product_image` varchar(255) DEFAULT 'no_image.png',
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `fk_product_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `sales`
--
CREATE TABLE `sales` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_sale_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sale_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

