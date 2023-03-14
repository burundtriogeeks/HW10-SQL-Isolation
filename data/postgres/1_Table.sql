-- public.users definition

-- Drop table

-- DROP TABLE public.users;

CREATE TABLE public.users (
                              id int4 NOT NULL,
                              "name" char(50) NOT NULL,
                              age int4 NOT NULL
);
CREATE UNIQUE INDEX users_id_idx ON public.users USING btree (id);