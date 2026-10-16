Feature: A square only shorter heads can reach is not danger
  In order to take squares I would win head-to-head instead of flinching from them
  As the TechBash snake
  I treat a square as dangerous only when a head as long as me or longer can reach it
  These boards leave one SAFE square, so the move proves the rule whatever strategy is playing

  Scenario: Only a shorter head reaches the way out
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -UvV-------
      S-WxxX-----
      t----------
      t----------
      T----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "up"

  Scenario: An equal-length head next to the ring square ahead
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      ---UvvV----
      --S--------
      --t--------
      --t--------
      --T--------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "left"

  Scenario: One head as long as us keeps the square ahead dangerous
    Given the board is:
      """
      X----------
      x----------
      x----------
      W----------
      -UvV-------
      S----------
      t----------
      t----------
      T----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"
