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

# GIT

## Criar nova branch

Criar nova branch para desenvolvimento de novo recurso.

'''
git checkout -b nome-da-barnch
'''

## Merge de branch com ramo main

'''
git checkout main
git merge nome-da-branch
'''

## Excluir branch

'''
git branch -d hotfix
'''

## Excluir branch (forçar)

'''
git branch -D hotfix
'''

## Excluir branch remota

'''
git push origin --delete hotfix
'''