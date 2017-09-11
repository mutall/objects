select table_name, table_comment from information_schema.tables where table_schema='mutall_eureka_waters' and table_type='base table';

select 
    column_name,
    is_nullable,
    data_type,
    character_maximum_length,
    column_type,
    column_key,
    extra,
    column_comment
 from information_schema.columns where table_schema='mutall_eureka_waters' and table_name='client';


-- Foreign k constrants (1)
select constraint_name, table_name  from information_schema.`TABLE_CONSTRAINTS` where table_schema='mutall_eureka_waters' and
constraint_type='foreign key';

--This has more information that (1) -- the referenced table name
select constraint_name, table_name, referenced_table_name from information_schema.`REFERENTIAL_CONSTRAINTS` where constraint_schema='mutall_eureka_waters';

select 
    usage.constraint_name, 
    usage.table_name, 
    usage.column_name, 
    usage.referenced_table_name, 
    usage.referenced_column_name 
from information_schema.`KEY_COLUMN_USAGE` as usage
     inner join(select 
                    constraint_name, 
                    table_name  
                from information_schema.`TABLE_CONSTRAINTS` 
                where table_schema='mutall_eureka_waters' and
                constraint_type='foreign key'
                ) as const 
            on usage.constraint_name=const.constraint_name    
where usage.constraint_schema='mutall_eureka_waters';

select 
                    constraint_name, 
                    table_name  
                from information_schema.`TABLE_CONSTRAINTS` 
                where table_schema='mutall_eureka_waters' and
                constraint_type='foreign key';
                

select 
    us.`table_name`, 
    us.`column_name`, 
    us.referenced_table_name, 
    us.referenced_column_name 
from information_schema.`KEY_COLUMN_USAGE` as us
     inner join information_schema.`TABLE_CONSTRAINTS` as const 
     on us.`constraint_name`=const.`constraint_name`
     and us.`table_name`=const.`table_name`
     and us.table_schema=const.table_schema
where us.table_schema='mutall_eureka_waters' and
      const.constraint_type='foreign key' and
      us.`table_name`='client';

select 

-- Identification indexes of a table
select 
    constraint_name, 
    table_name  
from information_schema.`TABLE_CONSTRAINTS` 
where table_schema='mutall_eureka_waters'
and constraint_type="unique";

--Columns in an index
select table_name, index_name, column_name from information_schema.`STATISTICS` 
where table_schema='mutall_eureka_waters'
and index_name like 'id%';


select 
                us.`table_name`, 
                us.`column_name`, 
                us.referenced_table_name, 
                us.referenced_column_name 
            from information_schema.`KEY_COLUMN_USAGE` as us
                 inner join information_schema.`TABLE_CONSTRAINTS` as const 
                 on us.`constraint_name`=const.`constraint_name`
                 and us.`table_name`=const.`table_name`
                 and us.table_schema=const.table_schema
            where us.table_schema='mutall_eureka_waters' and
                  const.constraint_type='foreign key';
                
SELECT 
`client_visit`.`client_visit` AS `_primary`, 
concat(`client_visit`.`date`,'/',`client`.`code`,'/',`client`.`full_name`) AS `_hint`, 
concat(`client_visit`.`date`,'/',`client`.`code`) AS `_id`, 

`client_ext`.`_primary` AS `client_ext_primary`, 
`client_ext`.`_hint` AS `client_ext_hint`, 
`client_ext`.`_id` AS `client_ext_id`, 
`client_meter_ext`.`_primary` AS `client_meter_ext_primary`, 
`client_meter_ext`.`_hint` AS `client_meter_ext_hint`, 
`client_meter_ext`.`_id` AS `client_meter_ext_id`, 
`client_visit`.`reading` AS `reading`, 
`client_visit`.`date` AS `date` 
FROM (((client_visit INNER JOIN `client` ON `client`.`client`=`client_visit`.`client`) LEFT JOIN (SELECT `client`.`client` AS `_primary`, concat(`client`.`code`,'/',`client`.`full_name`) AS `_hint`, concat(`client`.`code`) AS `_id` FROM client ) AS `client_ext` ON `client_ext`.`_primary`=`client_visit`.`client`) LEFT JOIN (SELECT `client_meter`.`client_meter` AS `_primary`, concat(`client_meter`.`serial_no`,'/',`client_meter`.`client_fullname`) AS `_hint`, concat(`client_meter`.`serial_no`) AS `_id` FROM client_meter ) AS `client_meter_ext` ON `client_meter_ext`.`_primary`=`client_visit`.`client_meter`) 
WHERE concat(`client_visit`.`date`,'/',`client`.`code`,'/',`client`.`full_name`) like '%c%';
