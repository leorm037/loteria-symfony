# Loteria (PHP Symfony)

# Database

## Criar usuário

```
GRANT ALL PRIVILEGES ON loteria.* TO 'loteria'@'localhost' identified by 'password';
FLUSH PRIVILEGES;
```

## Dump table

'''
// mysqldump db_name tb_name --result-file=dump.sql
mysqldump loteria loteria concurso --result-file=dump.sql
'''