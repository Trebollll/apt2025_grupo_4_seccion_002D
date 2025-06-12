-- Crear tabla de regiones
CREATE TABLE region (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- Crear tabla de comunas
CREATE TABLE comuna (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    region_id INT NOT NULL,
    FOREIGN KEY (region_id) REFERENCES region(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Script para insertar datos en la tabla region
INSERT INTO region (nombre) VALUES
('Región de Arica y Parinacota'),
('Región de Tarapacá'),
('Región de Antofagasta'),
('Región de Atacama'),
('Región de Coquimbo'),
('Región de Valparaíso'),
('Región Metropolitana de Santiago'),
('Región del Libertador General Bernardo O’Higgins'),
('Región del Maule'),
('Región de Ñuble'),
('Región del Biobío'),
('Región de La Araucanía'),
('Región de Los Ríos'),
('Región de Los Lagos'),
('Región de Aysén del General Carlos Ibáñez del Campo'),
('Región de Magallanes y de la Antártica Chilena');

-- Script para insertar datos en la tabla comuna
-- Región 1: Arica y Parinacota
INSERT INTO comuna (nombre, region_id) VALUES ('Arica', 1);
INSERT INTO comuna (nombre, region_id) VALUES ('Camarones', 1);
INSERT INTO comuna (nombre, region_id) VALUES ('Putre', 1);
INSERT INTO comuna (nombre, region_id) VALUES ('General Lagos', 1);

-- Región 2: Tarapacá
INSERT INTO comuna (nombre, region_id) VALUES ('Iquique', 2);
INSERT INTO comuna (nombre, region_id) VALUES ('Alto Hospicio', 2);
INSERT INTO comuna (nombre, region_id) VALUES ('Pozo Almonte', 2);
INSERT INTO comuna (nombre, region_id) VALUES ('Camiña', 2);
INSERT INTO comuna (nombre, region_id) VALUES ('Colchane', 2);
INSERT INTO comuna (nombre, region_id) VALUES ('Huara', 2);
INSERT INTO comuna (nombre, region_id) VALUES ('Pica', 2);

-- Región 3: Antofagasta
INSERT INTO comuna (nombre, region_id) VALUES ('Antofagasta', 3);
INSERT INTO comuna (nombre, region_id) VALUES ('Mejillones', 3);
INSERT INTO comuna (nombre, region_id) VALUES ('Sierra Gorda', 3);
INSERT INTO comuna (nombre, region_id) VALUES ('Taltal', 3);
INSERT INTO comuna (nombre, region_id) VALUES ('Calama', 3);
INSERT INTO comuna (nombre, region_id) VALUES ('Ollagüe', 3);
INSERT INTO comuna (nombre, region_id) VALUES ('San Pedro de Atacama', 3);
INSERT INTO comuna (nombre, region_id) VALUES ('Tocopilla', 3);
INSERT INTO comuna (nombre, region_id) VALUES ('María Elena', 3);

-- Región 4: Atacama
INSERT INTO comuna (nombre, region_id) VALUES ('Copiapó', 4);
INSERT INTO comuna (nombre, region_id) VALUES ('Caldera', 4);
INSERT INTO comuna (nombre, region_id) VALUES ('Tierra Amarilla', 4);
INSERT INTO comuna (nombre, region_id) VALUES ('Chañaral', 4);
INSERT INTO comuna (nombre, region_id) VALUES ('Diego de Almagro', 4);
INSERT INTO comuna (nombre, region_id) VALUES ('Vallenar', 4);
INSERT INTO comuna (nombre, region_id) VALUES ('Freirina', 4);
INSERT INTO comuna (nombre, region_id) VALUES ('Huasco', 4);
INSERT INTO comuna (nombre, region_id) VALUES ('Alto del Carmen', 4);

-- Región 5: Coquimbo
INSERT INTO comuna (nombre, region_id) VALUES ('La Serena', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Coquimbo', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Andacollo', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('La Higuera', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Paiguano', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Vicuña', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Illapel', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Canela', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Los Vilos', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Salamanca', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Ovalle', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Combarbalá', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Monte Patria', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Punitaqui', 5);
INSERT INTO comuna (nombre, region_id) VALUES ('Río Hurtado', 5);

-- Región 6: Valparaíso
INSERT INTO comuna (nombre, region_id) VALUES ('Valparaíso', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Casablanca', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Concón', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Juan Fernández', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Puchuncaví', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Quintero', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Viña del Mar', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Isla de Pascua', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Los Andes', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Calle Larga', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Rinconada', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('San Esteban', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('La Ligua', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Cabildo', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Papudo', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Petorca', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Zapallar', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Quillota', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Calera', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Hijuelas', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('La Cruz', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Nogales', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('San Antonio', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Algarrobo', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Cartagena', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('El Quisco', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('El Tabo', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Santo Domingo', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('San Felipe', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Catemu', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Llaillay', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Panquehue', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Putaendo', 6);
INSERT INTO comuna (nombre, region_id) VALUES ('Santa María', 6);

-- Región 7: Metropolitana de Santiago
INSERT INTO comuna (nombre, region_id) VALUES ('Cerrillos', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Cerro Navia', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Conchalí', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('El Bosque', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Estación Central', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Huechuraba', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Independencia', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('La Cisterna', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('La Florida', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('La Granja', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('La Pintana', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('La Reina', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Las Condes', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Lo Barnechea', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Lo Espejo', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Lo Prado', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Macul', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Maipú', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Ñuñoa', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Pedro Aguirre Cerda', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Peñalolén', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Providencia', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Pudahuel', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Quilicura', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Quinta Normal', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Recoleta', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Renca', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('San Joaquín', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('San Miguel', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('San Ramón', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Santiago', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Vitacura', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Puente Alto', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Pirque', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('San José de Maipo', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Colina', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Lampa', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Tiltil', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('San Bernardo', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Buin', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Calera de Tango', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Paine', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Melipilla', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Alhué', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Curacaví', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('María Pinto', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('San Pedro', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Talagante', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('El Monte', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Isla de Maipo', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Padre Hurtado', 7);
INSERT INTO comuna (nombre, region_id) VALUES ('Peñaflor', 7);

-- Región 8: O’Higgins
INSERT INTO comuna (nombre, region_id) VALUES ('Rancagua', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Codegua', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Coinco', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Coltauco', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Doñihue', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Graneros', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Las Cabras', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Machalí', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Malloa', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Mostazal', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Olivar', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Peumo', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Pichidegua', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Quinta de Tilcoco', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Rengo', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Requínoa', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('San Vicente', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Pichilemu', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('La Estrella', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Litueche', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Marchigüe', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Navidad', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Paredones', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('San Fernando', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Chépica', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Chimbarongo', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Lolol', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Nancagua', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Palmilla', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Peralillo', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Placilla', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Pumanque', 8);
INSERT INTO comuna (nombre, region_id) VALUES ('Santa Cruz', 8);

-- Región 9: Maule
INSERT INTO comuna (nombre, region_id) VALUES ('Talca', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('San Clemente', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Pelarco', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Pencahue', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Maule', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('San Rafael', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Curepto', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Constitución', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Empedrado', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Río Claro', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Linares', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Colbún', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Longaví', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Parral', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Retiro', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('San Javier', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Villa Alegre', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Yerbas Buenas', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Curicó', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Hualañé', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Licantén', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Molina', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Rauco', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Romeral', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Sagrada Familia', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Teno', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Vichuquén', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Cauquenes', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Chanco', 9);
INSERT INTO comuna (nombre, region_id) VALUES ('Pelluhue', 9);

-- Región 10: Ñuble
INSERT INTO comuna (nombre, region_id) VALUES ('San Carlos', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('San Fabián', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('San Nicolás', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Ñiquén', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Coihueco', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Chillán', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Chillán Viejo', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('El Carmen', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Pemuco', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Yungay', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Bulnes', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Quillón', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Ránquil', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Pinto', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Treguaco', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Cobquecura', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Coelemu', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Ninhue', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Portezuelo', 10);
INSERT INTO comuna (nombre, region_id) VALUES ('Quirihue', 10);

-- Región 11: Biobío
INSERT INTO comuna (nombre, region_id) VALUES ('Concepción', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Coronel', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Chiguayante', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Florida', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Hualqui', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Lota', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Penco', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('San Pedro de la Paz', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Santa Juana', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Talcahuano', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Tomé', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Hualpén', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Lebu', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Arauco', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Cañete', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Contulmo', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Curanilahue', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Los Álamos', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Tirúa', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Los Ángeles', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Antuco', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Cabrero', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Laja', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Mulchén', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Nacimiento', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Negrete', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Quilaco', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Quilleco', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('San Rosendo', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Santa Bárbara', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Tucapel', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Yumbel', 11);
INSERT INTO comuna (nombre, region_id) VALUES ('Alto Biobío', 11);

-- Región 12: La Araucanía
INSERT INTO comuna (nombre, region_id) VALUES ('Temuco', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Carahue', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Cunco', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Curarrehue', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Freire', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Galvarino', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Gorbea', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Lautaro', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Loncoche', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Melipeuco', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Nueva Imperial', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Padre Las Casas', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Perquenco', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Pitrufquén', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Pucón', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Saavedra', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Teodoro Schmidt', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Toltén', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Vilcún', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Villarrica', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Cholchol', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Angol', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Collipulli', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Curacautín', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Ercilla', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Lonquimay', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Los Sauces', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Lumaco', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Purén', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Renaico', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Traiguén', 12);
INSERT INTO comuna (nombre, region_id) VALUES ('Victoria', 12);

-- Región 13: Los Ríos
INSERT INTO comuna (nombre, region_id) VALUES ('Valdivia', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Corral', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Lanco', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Los Lagos', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Máfil', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Mariquina', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Paillaco', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Panguipulli', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('La Unión', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Futrono', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Lago Ranco', 13);
INSERT INTO comuna (nombre, region_id) VALUES ('Río Bueno', 13);

-- Región 14: Los Lagos
INSERT INTO comuna (nombre, region_id) VALUES ('Puerto Montt', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Calbuco', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Cochamó', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Fresia', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Frutillar', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Los Muermos', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Llanquihue', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Maullín', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Puerto Varas', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Castro', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Ancud', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Chonchi', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Curaco de Vélez', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Dalcahue', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Puqueldón', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Queilén', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Quellón', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Quemchi', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Quinchao', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Osorno', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Puerto Octay', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Purranque', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Puyehue', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Río Negro', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('San Juan de la Costa', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('San Pablo', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Chaitén', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Futaleufú', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Hualaihué', 14);
INSERT INTO comuna (nombre, region_id) VALUES ('Palena', 14);

-- Región 15: Aysén
INSERT INTO comuna (nombre, region_id) VALUES ('Coyhaique', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('Lago Verde', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('Aisén', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('Cisnes', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('Guaitecas', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('Cochrane', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('O’Higgins', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('Tortel', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('Chile Chico', 15);
INSERT INTO comuna (nombre, region_id) VALUES ('Río Ibáñez', 15);

-- Región 16: Magallanes y la Antártica Chilena
INSERT INTO comuna (nombre, region_id) VALUES ('Punta Arenas', 16);
INSERT INTO comuna (nombre, region_id) VALUES ('Laguna Blanca', 16);
INSERT INTO comuna (nombre, region_id) VALUES ('Río Verde', 16);
INSERT INTO comuna (nombre, region_id) VALUES ('San Gregorio', 16);
INSERT INTO comuna (nombre, region_id) VALUES ('Porvenir', 16);
INSERT INTO comuna (nombre, region_id) VALUES ('Primavera', 16);
INSERT INTO comuna (nombre, region_id) VALUES ('Timaukel', 16);
INSERT INTO comuna (nombre, region_id) VALUES ('Puerto Natales', 16);
INSERT INTO comuna (nombre, region_id) VALUES ('Torres del Paine', 16);

-- Crear tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    rut VARCHAR(12) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    edad INT DEFAULT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    genero VARCHAR(20) DEFAULT NULL,
    direccion TEXT NOT NULL,
    numero_contacto VARCHAR(20) NOT NULL,
    representante VARCHAR(100) DEFAULT NULL,
    horario VARCHAR(100) DEFAULT NULL,
    descripcion TEXT DEFAULT NULL,
    especies TEXT DEFAULT NULL,
    redes_sociales TEXT DEFAULT NULL,
    mapa_url TEXT DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    region_id INT NOT NULL,
    comuna_id INT NOT NULL,
    tipo VARCHAR(20) NOT NULL,

    FOREIGN KEY (region_id) REFERENCES region(id),
    FOREIGN KEY (comuna_id) REFERENCES comuna(id)
);

-- Crear tabla de mascotas
CREATE TABLE mascotas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estado_salud VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    sexo VARCHAR(20) NOT NULL,
    raza VARCHAR(100) NOT NULL,
    especie VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    nro_chip VARCHAR(50) NOT NULL,
    tamaño VARCHAR(50) NOT NULL,

    refugio_id INT NOT NULL,
    adoptante_id INT DEFAULT NULL,
    region_id INT NOT NULL,
    comuna_id INT NOT NULL,

    FOREIGN KEY (refugio_id) REFERENCES usuarios(id),
    FOREIGN KEY (adoptante_id) REFERENCES usuarios(id),
    FOREIGN KEY (region_id) REFERENCES region(id),
    FOREIGN KEY (comuna_id) REFERENCES comuna(id)
);

-- Agregar 'estado' a la tabla mascotas
ALTER TABLE mascotas
ADD COLUMN estado VARCHAR(20) NOT NULL DEFAULT 'No adoptada';

-- Crear tabla de Documentos
CREATE TABLE documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla de Solicitudes
CREATE TABLE `solicitudes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mascota_id` INT(11) NOT NULL,
  `refugio_id` INT(11) NOT NULL,
  `adoptante_id` INT(11) NOT NULL,
  `estado` ENUM('En espera', 'Aceptado', 'Rechazado') NOT NULL DEFAULT 'En espera',
  `fecha_solicitud` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_solicitud_mascota` (`mascota_id`),
  KEY `fk_solicitud_refugio` (`refugio_id`),
  KEY `fk_solicitud_adoptante` (`adoptante_id`),
  CONSTRAINT `fk_solicitud_adoptante` FOREIGN KEY (`adoptante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_solicitud_mascota` FOREIGN KEY (`mascota_id`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_solicitud_refugio` FOREIGN KEY (`refugio_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear tabla de Seguimiento de Mascotas
CREATE TABLE `seguimiento_mascotas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mascota_id` INT(11) NOT NULL,
  `titulo` VARCHAR(255) DEFAULT NULL,
  `fecha` DATE NOT NULL,
  `descripcion` TEXT NOT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mascota_id` (`mascota_id`),
  CONSTRAINT `seguimiento_mascotas_ibfk_1` FOREIGN KEY (`mascota_id`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
