final class SomeClassMother
{
	public string $s;
	public ?int $i = null;
	public float $f = 1.1;
	public int|float $if = 1;


	public function withS(string $s): self
	{
		$this->s=$s; return $this;
	}


	public function withI(?int $i): self
	{
		$this->i=$i; return $this;
	}


	public function withF(float $f): self
	{
		$this->f=$f; return $this;
	}


	public function withIf(int|float $if): self
	{
		$this->if=$if; return $this;
	}


	public static function any(): Tests\MotherOfAllObjects\Stub\SomeClass
	{
		return self::newObject()->create();
	}


	public function create(): Tests\MotherOfAllObjects\Stub\SomeClass
	{
		return new Tests\MotherOfAllObjects\Stub\SomeClass($this->s,$this->i,$this->f,$this->if);
	}


	private function __construct(string $s, ?int $i = null, float $f = 1.1, int|float $if = 1)
	{
		$this->s=$s;
		$this->i=$i;
		$this->f=$f;
		$this->if=$if;
	}
}
