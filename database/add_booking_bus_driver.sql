-- Migration: booking_bus_driver mapping table to support multiple drivers per bus per booking
CREATE TABLE IF NOT EXISTS `booking_bus_driver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_booking_bus_driver` (`booking_id`,`bus_id`,`driver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


