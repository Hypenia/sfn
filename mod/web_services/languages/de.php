<?php
return array(
	'APIException:ApiResultUnknown' => "Der Typ der API-Rückgabe ist unbekannt. Das sollte nicht passieren.",
	'APIException:MissingParameterInMethod' => "Fehlender Parameter %s in Methode %s.",
	'APIException:ParameterNotArray' => "%s scheint kein Feld zu sein.",
	'APIException:UnrecognisedTypeCast' => "Unbekannter Typ in Cast %s für Variable '%s' in Methode '%s'.",
	'APIException:InvalidParameter' => "Ungültiger Parameter für '%s' in Methode '%s' gefunden.",
	'APIException:FunctionParseError' => "%s(%s) ergab einen Parsing-Fehler.",
	'APIException:FunctionNoReturn' => "%s(%s) lieferte keinen Rückgabewert.",
	'APIException:APIAuthenticationFailed' => "Beim Aufruf der Methode schlug die API-Authentifizierung fehl.",
	'APIException:UserAuthenticationFailed' => "Beim Aufruf der Methode schlug die Benutzer-Authentifizierung fehl.",
	'APIException:MethodCallNotImplemented' => "Der Methoden-Aufruf '%s' ist nicht implementiert.",
	'APIException:FunctionDoesNotExist' => "Die Funktion für die Methode '%s' kann nicht aufgerufen werden.",
	'APIException:AlgorithmNotSupported' => "Algorithmus '%s' wird nicht unterstützt oder wurde deaktiviert.",
	'APIException:NotGetOrPost' => "Die Anfrage-Methode muß GET oder POST sein.",
	'APIException:MissingAPIKey' => "Fehlender API-Schlüssel.",
	'APIException:BadAPIKey' => "Ungültiger API-Schlüssel.",
	'APIException:MissingHmac' => "Fehlender X-Elgg-hmac Header.",
	'APIException:MissingHmacAlgo' => "Fehlender X-Elgg-hmac-algo Header.",
	'APIException:MissingTime' => "Fehlender X-Elgg-time Header.",
	'APIException:MissingNonce' => "Fehlender X-Elgg-nonce Header.",
	'APIException:TemporalDrift' => "Epoch-Fehler: X-Elgg-time liegt zu weit in der Vergangenheit oder Zukunft.",
	'APIException:NoQueryString' => "Keine Daten im Query-String.",
	'APIException:MissingPOSTHash' => "Fehlender X-Elgg-posthash Header.",
	'APIException:MissingPOSTAlgo' => "Fehlender X-Elgg-posthash_algo Header.",
	'APIException:MissingContentType' => "Content-Typ für POST-Daten fehlt.",
	'SecurityException:APIAccessDenied' => "Entschuldigung, der API-Zugriff wurde durch den Administrator deaktiviert.",
	'SecurityException:NoAuthMethods' => "Es konnte keine Authentifizierungs-Methode gefunden werden, um diesen API-Zugriff zu authentifizieren.",
	'SecurityException:authenticationfailed' => "Der Benutzer konnte nicht authentifiziert werden.",
	'InvalidParameterException:APIMethodOrFunctionNotSet' => "Die Methode oder Funktion wurde im Aufruf in expose_method() nicht gesetzt.",
	'InvalidParameterException:APIParametersArrayStructure' => "Die Parameter-Feldstruktur im Aufruf von Expose-Methode '%s' ist falsch.",
	'InvalidParameterException:UnrecognisedHttpMethod' => "Unbekannte Http-Methode %s für API-Mmethode '%s'.",
	'SecurityException:AuthTokenExpired' => "Entweder fehlt das Authentifizierungs-Token oder es ist ungültig oder abgelaufen.",
	'SecurityException:InvalidPostHash' => "POST-Daten-Hash ist ungültig - Erwartet wurde %s aber %s erhalten.",
	'SecurityException:DupePacket' => "Packet-Signatur ist schon von früher bekannt.",
	'SecurityException:InvalidAPIKey' => "Ungültiger oder fehlender API-Schlüssel.",
	'NotImplementedException:CallMethodNotImplemented' => "Der Methoden-Aufruf '%s' wird derzeit nicht unterstützt.",
	'CallException:InvalidCallMethod' => "%s muß unter Verwendung von '%s' aufgerufen werden.",

	'system.api.list' => "Liste alle im System verfügbaren API-Aufrufe auf.",
	'auth.gettoken' => "Dieser API-Aufruf ermöglicht es einem Benutzer ein Authentifizierungs-Token zu beziehen, das für die Authentifizierung nachfolgender API-Aufrufe verwendet werden kann. Übergebe es als Parameter auth_token.",
	
	// plugin settings
	'web_services:settings:authentication' => "Authentifizierungs-Einstellungen der Web-API",
	'web_services:settings:authentication:description' => "Einige API-Methoden benötigen eine Authentifizierung der externen Quellen. Diese externen Quellen benötigen dazu ein API-Schlüsselpaar (öffentlicher und privater Schlüssel).

Bitte beachte, dass mindestens eine API-Authentifizierungsmethode aktiviert sein muss, damit die Authentifizierung von API-Anfragen funktionieren kann.",
	'web_services:settings:authentication:allow_key' => "Erlaube die einfache API-Authentifizierung mit einem öffentlichen Schlüssel",
	'web_services:settings:authentication:allow_key:help' => "Der öffentliche API-Schlüssel kann bei einer Anfrage als Parameter mit übergeben werden.",
	'web_services:settings:authentication:allow_hmac' => "Erlaube die API-Authentifizierung mit HMAC-Header",
	'web_services:settings:authentication:allow_hmac:help' => "Bei der HMAC-Authentifizierung müssen bei einer Anfrage spezifische Header verwendet werden, damit die Authentizität der Anfrage sicher gestellt werden kann.",
);