USE cortefacil;

UPDATE usuarios SET senha = '$2a$10$ItZmdOKme/i43NMagTlHQOVrxkmapTbRc7Wm/CimuO2URNbc19M42' WHERE email = 'admin@teste.com';
UPDATE usuarios SET senha = '$2a$10$NTvfgkojhOIkcEg5g4hGguqwzTTiHWU.7/iGDicIT6ju1IdVSedJi' WHERE email = 'cliente@teste.com';
UPDATE usuarios SET senha = '$2a$10$1vbMjxXC5OjJYwXleIQy9OWf/r7iTnCrCcB67uMpGU5fB4E/2Nc5i' WHERE email = 'parceiro@teste.com';

SELECT id, nome, email, LEFT(senha, 30) as senha_preview FROM usuarios WHERE email IN ('admin@teste.com', 'cliente@teste.com', 'parceiro@teste.com');