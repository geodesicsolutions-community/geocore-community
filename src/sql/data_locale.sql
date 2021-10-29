# Default table data for the following tables:
#  -geodesic_currency_types
#  -geodesic_pages_languages
#  -geodesic_payment_types
#  -geodesic_price_plan_ad_lengths
#  -geodesic_text_badwords
#  -geodesic_region_level
#  -geodesic_region_level_labels

--
-- Dumping data for table `geodesic_currency_types`
--

INSERT INTO `geodesic_currency_types` (`type_id`, `type_name`, `precurrency`, `postcurrency`, `conversion_rate`, `display_order`) VALUES
(12, 'US Dollars', '$', 'USD', 1, 1),
(22, 'British Pounds', '&pound;', 'GBP', 1, 6),
(20, 'European Euro', '&euro;', 'EUR', 1, 20),
(21, 'Canadian Dollars ', '$', 'CAD', 1, 10),
(23, 'Australian Dollars ', '$', 'AUD', 1, 4),
(16, 'New Zealand Dollars', '$', 'NZD', 1, 40),
(17, 'Singapore Dollars', '$', 'SGD', 1, 50),
(19, 'Japanese Yen', '&yen;', 'JPY', 1, 30),
(25, 'Make Offer', '', 'Make Offer', 1, 97);


--
-- Dumping data for table `geodesic_pages_languages`
--

INSERT INTO `geodesic_pages_languages` (`language_id`, `language`, `browser_label`, `default_language`, `active`, `charset`) VALUES
(1, 'English', '', 1, 1, 'ISO-8859-1');

--
-- Dumping data for table `geodesic_payment_types`
--

INSERT INTO `geodesic_payment_types` (`type_id`, `type_name`, `display_order`) VALUES
(5, 'Mastercard', 10),
(4, 'Visa', 5),
(6, 'Discover', 15),
(7, 'American Express', 20),
(8, 'Check', 25),
(9, 'Money Order', 30),
(10, 'PayPal', 35),
(11, 'Bank Transfer', 40);

--
-- Dumping data for table `geodesic_price_plan_ad_lengths`
--

INSERT INTO `geodesic_price_plan_ad_lengths` (`length_id`, `price_plan_id`, `category_id`, `length_of_ad`, `display_length_of_ad`, `length_charge`, `renewal_charge`) VALUES
(11, 1, 0, 7, '1 week', 5.00, 3.00),
(4, 1, 11, 7, '1 week', 1.00, 0.00),
(5, 1, 11, 14, '2 weeks', 2.00, 0.00),
(6, 1, 11, 31, '1 month', 3.00, 0.00),
(7, 1, 11, 62, '2 months', 5.00, 0.00),
(8, 1, 15, 7, '1 week', 1.00, 0.00),
(9, 1, 15, 14, '2 weeks', 2.00, 0.00),
(10, 1, 15, 31, '1 month', 3.00, 0.00);

--
-- Dumping data for table `geodesic_text_badwords`
--

INSERT INTO `geodesic_text_badwords` (`badword_id`, `badword`, `badword_replacement`, `entire_word`) VALUES
(2, 'asdf', 'asdf', 0);

--
-- Dumping data for table `geodesic_region_level`
--

INSERT INTO `geodesic_region_level` (`level`, `region_type`, `use_label`, `always_show`) VALUES
(1, 'country', 'yes', 'no'),
(2, 'state/province', 'yes', 'no'),
(3, 'other', 'yes', 'no');

INSERT INTO `geodesic_region_level_labels` (`level`, `language_id`, `label`) VALUES
(1, 1, 'Country'),
(2, 1, 'State'),
(3, 1, 'Metro');
