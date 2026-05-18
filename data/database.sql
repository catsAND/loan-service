DROP TABLE IF EXISTS public.applications;
DROP TABLE IF EXISTS public.clients;

CREATE TABLE public.clients (
    id uuid NOT NULL,
    first_name character varying(32) NOT NULL,
    last_name character varying(32) NOT NULL,
    email text NOT NULL,
    phone character varying(16) NOT NULL,
    created_at timestamp with time zone NOT NULL,
    updated_at timestamp with time zone NOT NULL,
    deleted_at timestamp with time zone
);

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_pkey PRIMARY KEY (id);

CREATE INDEX idx_clients_active_created_at ON public.clients USING btree (created_at DESC) WHERE (deleted_at IS NULL);
CREATE UNIQUE INDEX idx_clients_active_email ON public.clients USING btree (email) WHERE (deleted_at IS NULL);
CREATE UNIQUE INDEX idx_clients_active_phone ON public.clients USING btree (phone) WHERE (deleted_at IS NULL);

CREATE TABLE public.applications (
    id uuid NOT NULL,
    client_id uuid NOT NULL,
    term smallint NOT NULL,
    amount numeric(10,2) NOT NULL,
    currency character(3) NOT NULL,
    created_at timestamp with time zone NOT NULL,
    updated_at timestamp with time zone NOT NULL,
    deleted_at timestamp with time zone
);

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT applications_pkey PRIMARY KEY (id);

CREATE INDEX idx_applications_active_created_at ON public.applications USING btree (created_at DESC) WHERE (deleted_at IS NULL);

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT "FK__clients" FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE RESTRICT;

CREATE INDEX idx_applications_client_id ON public.applications (client_id);
