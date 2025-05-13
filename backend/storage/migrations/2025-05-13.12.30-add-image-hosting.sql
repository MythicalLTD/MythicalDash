ALTER TABLE mythicaldash_users
ADD COLUMN image_hosting_enabled enum('false', 'true') NOT NULL DEFAULT 'false',
ADD COLUMN image_hosting_embed_enabled enum('false', 'true') NOT NULL DEFAULT 'false',
ADD COLUMN image_hosting_embed_title text DEFAULT NULL,
ADD COLUMN image_hosting_embed_description text DEFAULT NULL,
ADD COLUMN image_hosting_embed_color text DEFAULT NULL,
ADD COLUMN image_hosting_embed_image text DEFAULT NULL,
ADD COLUMN image_hosting_embed_thumbnail text DEFAULT NULL,
ADD COLUMN image_hosting_embed_url text DEFAULT NULL,
ADD COLUMN image_hosting_embed_author_name text DEFAULT NULL,
ADD COLUMN image_hosting_upload_key text DEFAULT NULL;
