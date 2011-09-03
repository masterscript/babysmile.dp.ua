update items set url = concat('/catalog', url)
where url not like '/catalog/%' and (type = 'good' or template = 'category');