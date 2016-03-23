<?php
	header('Access-Control-Allow-Origin: *');
	require 'vendor/autoload.php';

	$settings = ['settings' => [
		'dsn' => 'sqlite:data/cellar.db',
		'displayErrorDetails' => true
	]];

	$app = new Slim\App($settings);

	$container = $app->getContainer();
	$container['pdo'] = function ($c) {
		return new PDO($c['settings']['dsn']);
	};

	$app->get('/ping', function ($request, $response) {
		$body = json_encode(['ack' => time()]);
		$response->write($body);
		$response = $response->withHeader('Content-Type', 'application/json');

		return $response;
	});

	$app->group('/v1', function () {

		$this->get('/install', function() {
			$dbh = $this->get('pdo');

			$schemaSql = file_get_contents(dirname(dirname(__FILE__)) . '/data/scripts/schema.sqlite.sql');
			$dataSql = file_get_contents(dirname(dirname(__FILE__)) . '/data/scripts/data.sqlite.sql');

			$dbh->exec($schemaSql);
			$dbh->exec($dataSql);
		});

		$this->get('/wines', function ($request, $response) {
			$sql = "SELECT * FROM wine ORDER BY name";
			try {
				$db = $this->get('pdo');
				$stmt = $db->query($sql);
				$wines = $stmt->fetchAll(PDO::FETCH_OBJ);

				if(!$wines){
					$response = $response->withStatus(404);
				}else {
					$response = $response->withJson($wines, 200);
				}

				return $response;
			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() .'}}', 500);
				return $response;
			}
		});

		$this->get('/wines/{id:[0-9]+}',	function ($request, $response, $args) {
			$id = $args['id'];
			$sql = "SELECT * FROM wine WHERE id=:id";
			try {
				$db = $this->get('pdo');
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("id", $id);
				$stmt->execute();
				$wine = $stmt->fetchObject();

				if(!$wine){
					$response = $response->withStatus(404);
				}else {
					$response = $response->withJson($wine, 200);
				}
				
				return $response;
			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() .'}}', 500);
				return $response;
			}
		});

		$this->get('/wines/search', function ($request, $response, $args) {
			$query = $request->getQueryParams();
			$column = key($query);

			$sql = "SELECT * FROM wine WHERE $column LIKE :value ORDER BY name";
			
			try {
				$db = $this->get('pdo');
				$stmt = $db->prepare($sql);
				$value = "%" . $query[$column] . "%";
				$stmt->bindParam("value", $value);
				$stmt->execute();
				$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
				
				if(!$wines){
					$response = $response->withStatus(404);
				}else {
					$response = $response->withJson($wines, 200);
				}
							
				return $response;
			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() .'}}', 500);
				return $response;
			}
		});

		$this->post('/wines', function ($request, $response, $args) {

			$wine = $request->getParsedBody();

			$sql = "INSERT INTO wine (name, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description)";

			try {
				$db = $this->get('pdo');

				$stmt = $db->prepare($sql);
				$stmt->execute($wine);
				$wine['id'] = $db->lastInsertId();

				$response = $response->withJson($wine, 201);

				return $response;
			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() .'}}', 500);
				return $response;
			}
		});

		$this->put('/wines/{id}', function($request, $response, $args) {
			$id = $args['id'];
			
			$wine = $request->getParsedBody();
			$wine["id"] = $id;

			$sql = "UPDATE wine SET name=:name, grapes=:grapes, country=:country, region=:region, year=:year, description=:description WHERE id=:id";

			try {
				$db = $this->get('pdo');
				$stmt = $db->prepare($sql);
				$stmt->execute($wine);

				if(!$wine){
					$response = $response->withStatus(404);
				}else {
					$response = $response->withJson($wine, 200);
				}
				
				return $response;
			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() .'}}', 500);
				return $response;
			}
		});

		$this->delete('/wines/{id}',	function($request, $response, $args) {
			$id = $args['id'];

			$sql = "DELETE FROM wine WHERE id=:id";
			try {
				$db = $this->get('pdo');
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("id", $id);
				$stmt->execute();

				$response->withStatus(200);

				return $response;
			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() .'}}', 500);
				return $response;
			}
		});

	});

	$app->run();
