<?php
	require_once "article_model.php";
	require_once "../classes/article.php";
	require_once "model.php";
	require_once "../classes/limiter.php";

	class ArticleModelDatabase extends ArticleModel
	{
		use ArticleInfoValidation;
		
		private $database;
		private $limiter;

		public function __construct()
		{
			$connectionInfo = parse_ini_file("../db/db.ini");
			$this->database = new PDO('mysql:host='.$connectionInfo['host'].';dbname='.$connectionInfo['dbname'],
				$connectionInfo['user'],
				$connectionInfo['password'],
				[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
			);
			$this->limiter = new Limiter($this->database, "articles");
		}
		public function getNArticles(int $offset, int $number)
		{
			$articles = [];
			
			//sql
			$query = 'SELECT id, title, creationdate, authoruid, content
					  FROM articles 
					  ORDER BY str_to_date(creationdate, "%d.%m.%Y Ð² %H:%i:%s") DESC 
					  LIMIT :offset,:number';
			$stmt = $this->database->prepare($query);
			$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindValue(':number', $number, PDO::PARAM_INT);
			$stmt->execute();
			
			//fetch data
			while($art = $stmt->fetch(PDO::FETCH_ASSOC)){
				$authorName = (getUserModelInstance())->getUserByID($art['authoruid'])->getUserName();
				$articles[] = new Article($art['id'], $art['title'], $art['content'], $art['authoruid'], $authorName, $art['creationdate']);
			}
			
			return $articles;
		}
		public function getArticle(int $id)
		{
			//sql
			$query = 'SELECT id, title, creationdate, authoruid, content
					  FROM articles
					  WHERE id = :id';
			$stmt = $this->database->prepare($query);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->execute();

			//fetch data
			if($stmt->rowCount()) {
				$row = $stmt->fetch();
				$authorName = (getUserModelInstance())->getUserByID($row['authoruid'])->getUserName();
				return new Article($row['id'], $row['title'], $row['content'], $row['authoruid'], $authorName, $row['creationdate']);
			}
			else
				return false;
		}
		public function saveNewArticle(string $title, string $content)
		{
			$this->limiter->check();

			//validate and encode
			$userInputErrors = $this->validateNewArticleInfo($title, $content);
			if($userInputErrors)
				return $userInputErrors;
			$title = htmlspecialchars($title, ENT_QUOTES);
			$content = htmlspecialchars($content, ENT_QUOTES);

			//make Article object
			$userModel = getUserModelInstance();
			$uid = $userModel->getUserID();
			$article = new Article(null, $title, $content, $uid, null);

			//save to database
			$query = 'INSERT INTO articles
					  (title, creationdate, authoruid, content)
					  VALUES (:title, :creationdate, :authoruid, :content)';
			$statement = $this->database->prepare($query);
			$statement->execute(['title' => $article->title, 'creationdate' => $article->creationDate, 'authoruid' => $article->authorUID, 'content' => $article->content]);

			return $userInputErrors;
		}
		public function deleteArticle(int $id)
		{
			//get authorUID from DB
			$query = 'SELECT authoruid FROM articles
					  WHERE id = :id';
			$stmt = $this->database->prepare($query);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->execute();

			if($stmt->rowCount()) {
				$result = $stmt->fetch();
				$authorUID = (int)$result['authoruid'];

				$userModel = getUserModelInstance();
				$uid = $userModel->getUserID();

				//ensure that auhor and current user is the same person
				if($authorUID == $uid || $userModel->isAdmin()) {
					$query = 'DELETE FROM articles WHERE id = :id';
					$stmt = $this->database->prepare($query);
					$stmt->bindValue(':id', $id, PDO::PARAM_INT);
					$stmt->execute();

					return (bool)$stmt->rowCount();
				}
			}
			return false;
		}
		public function getNumberOfArticles() : int
		{
			//sql
			$query = 'SELECT COUNT(*) as count FROM articles';
			$stmt = $this->database->query($query);

			//fetch data
			return $stmt->fetch()['count'];
		}
	}
