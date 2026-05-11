USE pushin20_bikes;

-- ============================================================
-- FORNITORI
-- ============================================================
INSERT INTO fornitore (ragione_sociale, partita_iva, email) VALUES
('MotoRicambi Srl',        'IT01234567890', 'ordini@motoricambi.it'),
('Parts & Wheels SpA',     'IT09876543210', 'supply@partsandwheels.eu'),
('BikeParts Italia Srl',   'IT05555666677', 'info@bikepartsitalia.it'),
('Euro Moto Supply GmbH',  'DE123456789',   'orders@euromotosupply.de');

-- ============================================================
-- PEZZI DI RICAMBIO
-- ============================================================
INSERT INTO pezzo_ricambio (codice, nome, quantita_stock, prezzo_vendita, scorta_minima, id_fornitore) VALUES
('OIL-FILT-001', 'Filtro olio Honda CB/CB500',          12,  1800,  5, 1),
('AIR-FILT-002', 'Filtro aria Yamaha MT-07',             8,  2200,  3, 2),
('BRK-PAD-003',  'Pastiglie freno anteriori universali', 20,  3500,  8, 1),
('CHN-SET-004',  'Kit catena e pignoni 520',              6,  8900,  3, 2),
('SPK-PLG-005',  'Candele NGK CR8E (set 4)',             30,  1200, 10, 1),
('OIL-SEAL-006', 'Paraoli forcella 43mm',                15,  2800,  5, 3),
('BAT-GEL-007',  'Batteria gel 12V 10Ah',                9,  6500,  4, 3),
('TIRE-F-008',   'Pneumatico anteriore 120/70 ZR17',      5, 14500,  3, 4),
('TIRE-R-009',   'Pneumatico posteriore 180/55 ZR17',     4, 17200,  3, 4),
('BRK-DISC-010', 'Disco freno anteriore 320mm',           7,  9800,  3, 2),
('CLT-KIT-011',  'Kit frizione completo Ducati 797',      3, 22000,  2, 3),
('EXHST-012',    'Guarnizione scarico universale',       25,   850,  8, 1),
('LAMP-013',     'Lampada H7 55W (coppia)',              40,   700, 10, 1),
('HDLGHT-014',   'Faro anteriore LED Yamaha MT-07',       2, 18500,  2, 4),
('SUSP-015',     'Ammortizzatore posteriore KTM 390',     4, 31000,  2, 4);

-- ============================================================
-- ORDINI AI FORNITORI
-- ============================================================
INSERT INTO ordine (data_ordine, data_consegna_prevista, stato, importo_totale, id_fornitore) VALUES
('2025-01-05', '2025-01-15', 'consegnato', 43200, 1),
('2025-02-10', '2025-02-20', 'consegnato', 86000, 4),
('2025-03-20', '2025-03-30', 'inviato',    31500, 2),
('2025-04-01', '2025-04-12', 'bozza',      26000, 3);

-- ============================================================
-- DETTAGLI ORDINI
-- ============================================================
INSERT INTO dettaglio_ordine (id_ordine, id_pezzo, quantita, prezzo_unitario) VALUES
(1,  1, 10, 1500),
(1,  5, 20,  900),
(2,  8,  3, 12000),
(2,  9,  3, 14000),
(3,  4,  2,  7500),
(3, 11,  1, 18000),
(4,  7,  2,  5500),
(4,  6,  4,  2400);
