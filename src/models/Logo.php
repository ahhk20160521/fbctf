<?hh // strict

class Logo extends Model {
  private function __construct(
    private int $id,
    private int $used,
    private int $enabled,
    private int $protected,
    private int $custom,
    private string $name,
    private string $logo
  ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getLogo(): string {
    return $this->logo;
  }

  public function getUsed(): bool {
    return $this->used === 1;
  }

  public function getEnabled(): bool {
    return $this->enabled === 1;
  }

  public function getProtected(): bool {
    return $this->protected === 1;
  }

  public function getCustom(): bool {
    return $this->custom === 1;
  }

  // Check to see if the logo exists.
  public static async function genCheckExists(
    string $logo,
  ): Awaitable<bool> {
    $all_logos = await self::genAllEnabledLogos();
    foreach ($all_logos as $l) {
      if ($logo === $l->getName()) {
        return true;
      }
    }
    return false;
  }

  // Enable or disable logo by passing 1 or 0.
  public static async function genSetEnabled(
    int $logo_id,
    bool $enabled,
  ): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf(
      'UPDATE logos SET enabled = %d WHERE id = %d LIMIT 1',
      (int)$enabled,
      $logo_id,
    );
  }

  // Retrieve a random logo from the table.
  public static async function genRandomLogo(
  ): Awaitable<string> {
    $db = await self::genDb();

    $result = await $db->queryf(
      'SELECT name FROM logos WHERE enabled = 1 ORDER BY RAND() LIMIT 1',
    );

    invariant($result->numRows() === 1, 'Expected exactly one result');

    return $result->mapRows()[0]['name'];
  }

  // Logo model by name
  public static async function genLogo(
    string $name,
  ): Awaitable<Logo> {
    $db = await self::genDb();

    $result = await $db->queryf(
      'SELECT * FROM logos WHERE name = %s',
      $name,
    );

    invariant($result->numRows() === 1, 'Expected exactly one result');

    return self::logoFromRow($result->mapRows()[0]);
  }

  // All the logos.
  public static async function genAllLogos(
  ): Awaitable<array<Logo>> {
    $db = await self::genDb();

    $result = await $db->queryf(
      'SELECT * FROM logos',
    );

    $logos = array();
    foreach ($result->mapRows() as $row) {
      $logos[] = self::logoFromRow($row);
    }

    return $logos;
  }

  // All the enabled logos.
  public static async function genAllEnabledLogos(
  ): Awaitable<array<Logo>> {
    $db = await self::genDb();

    $result = await $db->queryf(
      'SELECT * FROM logos WHERE enabled = 1 AND protected = 0',
    );

    $logos = array();
    foreach ($result->mapRows() as $row) {
      $logos[] = self::logoFromRow($row);
    }

    return $logos;
  }

  // Create a logo and return the created logo id.
  public static async function genCreate(
    string $name,
    string $logo,
  ): Awaitable<int> {
    $db = await self::genDb();

    // Create logo
    await $db->queryf(
      'INSERT INTO logos (name, logo) VALUES (%s, %s)',
      $name,
      $logo,
    );

    // Return newly created logo_id
    $result = await $db->queryf(
      'SELECT id FROM logos WHERE name = %s AND logo = %s LIMIT 1',
      $name,
      $logo,
    );

    invariant($result->numRows() === 1, 'Expected exactly one result');
    return intval($result->mapRows()[0]['id']);
  }

  private static function logoFromRow(Map<string, string> $row): Logo {
    return new Logo(
      intval(must_have_idx($row, 'id')),
      intval(must_have_idx($row, 'used')),
      intval(must_have_idx($row, 'enabled')),
      intval(must_have_idx($row, 'protected')),
      intval(must_have_idx($row, 'custom')),
      must_have_idx($row, 'name'),
      must_have_idx($row, 'logo'),
    );
  }
}
