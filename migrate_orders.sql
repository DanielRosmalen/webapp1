-- Migratie: orders tabel bijwerken naar nieuwe structuur
-- Uitvoeren in phpMyAdmin (http://localhost:8080)

ALTER TABLE `orders`
  CHANGE `klant-naam` `klant_naam` TEXT NOT NULL,
  ADD COLUMN `telefoon`    VARCHAR(50)   NOT NULL DEFAULT ''     AFTER `klant_naam`,
  ADD COLUMN `opmerking`   TEXT          DEFAULT NULL            AFTER `ophaaltijd`,
  ADD COLUMN `producten`   TEXT          NOT NULL                AFTER `opmerking`,
  ADD COLUMN `totaal`      DECIMAL(8,2)  NOT NULL DEFAULT '0.00' AFTER `producten`,
  ADD COLUMN `besteldatum` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `totaal`;

ALTER TABLE `orders`
  ADD PRIMARY KEY (`ordernummer`),
  MODIFY `ordernummer` INT NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

-- Betaalmethode en betaalstatus toevoegen
ALTER TABLE `orders`
  ADD COLUMN `betaalmethode` ENUM('contant','pin')              NOT NULL DEFAULT 'contant'     AFTER `totaal`,
  ADD COLUMN `betaalstatus`  ENUM('niet_betaald','betaald')     NOT NULL DEFAULT 'niet_betaald' AFTER `betaalmethode`;
