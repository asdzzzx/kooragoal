CREATE TABLE IF NOT EXISTS leagues (
    id INT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    country VARCHAR(100),
    logo VARCHAR(255),
    flag VARCHAR(255),
    season INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teams (
    id INT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(10),
    country VARCHAR(100),
    founded INT,
    logo VARCHAR(255),
    venue JSON NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS players (
    id INT PRIMARY KEY,
    team_id INT,
    name VARCHAR(150),
    firstname VARCHAR(100),
    lastname VARCHAR(100),
    age INT,
    nationality VARCHAR(100),
    height VARCHAR(20),
    weight VARCHAR(20),
    photo VARCHAR(255),
    stats JSON,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fixtures (
    id INT PRIMARY KEY,
    league_id INT,
    season INT,
    round VARCHAR(100),
    date VARCHAR(50),
    status_long VARCHAR(50),
    status_short VARCHAR(10),
    timestamp BIGINT,
    referee VARCHAR(150),
    venue JSON,
    home_team_id INT,
    away_team_id INT,
    goals_home INT,
    goals_away INT,
    score JSON,
    FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE,
    FOREIGN KEY (home_team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (away_team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fixture_id INT,
    time_elapsed INT,
    team_id INT,
    player_id INT NULL,
    type VARCHAR(50),
    detail VARCHAR(100),
    comments VARCHAR(255),
    FOREIGN KEY (fixture_id) REFERENCES fixtures(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fixture_id INT,
    team_id INT,
    type VARCHAR(100),
    value JSON,
    FOREIGN KEY (fixture_id) REFERENCES fixtures(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS standings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    league_id INT,
    season INT,
    team_id INT,
    rank_position INT,
    points INT,
    goals_diff INT,
    stats JSON,
    UNIQUE KEY league_team (league_id, season, team_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS scorers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    league_id INT,
    season INT,
    player_id INT,
    team_id INT,
    goals INT,
    assists INT,
    stats JSON,
    UNIQUE KEY league_player (league_id, season, player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lineups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fixture_id INT,
    team_id INT,
    formation VARCHAR(20),
    start_xi JSON,
    substitutes JSON,
    FOREIGN KEY (fixture_id) REFERENCES fixtures(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS system_updates (
    task VARCHAR(150) PRIMARY KEY,
    last_run DATETIME,
    status ENUM('success', 'failed') DEFAULT 'success',
    message VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50),
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_login_attempts (
    username VARCHAR(100) PRIMARY KEY,
    attempts INT DEFAULT 0,
    last_attempt DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
