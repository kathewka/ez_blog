<?php
require_once 'FileNotFoundException.php';

/**
 * Entry.php
 *
 * エントリオブジェクト
 */
class Entry {
  /**
   * タイトル
   * @var string
   */
  public $title = '';
  /**
   * 内容
   * @var string
   */
  public $content = '';
  /**
   * タグ
   * @var array
   */
  public $tags = [];
  /**
   * 更新日時
   * @var Date
   */
  public $modified = null;
  /**
   * ファイル種別
   * @var string 'plain' or 'markdown'
   */
  public $type = null;

  /** ファイル種別＝プレーンテキスト */
  const TYPE_PLAIN = 'plain';
  /** ファイル種別＝マークダウン */
  const TYPE_MARKDOWN = 'markdown';

  /**
   * エントリリストを取得する
   *
   * @return array
   */
  static public function getEntries() {
    // 設定を読み込んでなかったら読み込んで
    require_once dirname(__FILE__) . '/../config.php';
    // ファイル毎にエントリ化
    chdir(ENTRIES_DIR);
    function setEntries($parent = '', $entries = []) {
      $handle = opendir($parent . '/');
      while( ($path = readdir($handle)) !== false ) {
        if (filetype("{$parent}/{$path}") == "file") {
          $entries[] = Entry::setEntry("{$parent}/{$path}");
        } elseif (preg_match('/^\./', $path) === 0) {
          $entries = setEntries("{$parent}/{$path}", $entries);
        }
      }
      return $entries;
    };
    $entries = setEntries('.');
    // 更新日の降順でソート
    usort($entries, function($a, $b) {
      if ($b->modified < $a->modified) {
          return -1;
      } else if ($a->modified < $b->modified) {
          return 1;
      } else {
          return 0;
      }
    });
    return $entries;
  }

  /**
   * ファイルパスからエントリを作成する
   *
   * @param string $path
   * @return Entry
   * @throws FileNotFoundException ファイルが見つからなかった時
   */
  static public function setEntry($path) {
    // ファイルの存在チェック
    if (!file_exists($path)) { throw new FileNotFoundException($path); }
    // ファイルの各種情報を取得して
    preg_match('/([^\/\]]+)\.\w{2,3}$/u', $path, $m);
    $title = end($m);
    $content = file_get_contents($path);
    preg_match_all('/\[([^\]]+)\]/u', $path, $m);
    $tags = end($m);
    $modified = new DateTime();
    $modified->setTimestamp(filemtime($path));
    preg_match('/\]?.+\.(\w{2,3})$/u', $path, $m);
    $type = $m[1] === 'md' ? Entry::TYPE_MARKDOWN : Entry::TYPE_PLAIN;
    // エントリを作成
    $entry = new Entry($title, $content, $tags, $modified, $type);
    return $entry;
  }

  /**
   * コンストラクタ
   *
   * @param string $title
   * @param string $content
   * @param array $tags
   * @param Date $modified
   * @param string $type
   */
  public function __construct($title, $content, $tags, $modified, $type) {
    $this->title = $title;
    $this->content = $content;
    $this->tags = $tags;
    $this->modified = $modified;
    $this->type = $type;
  }
}
