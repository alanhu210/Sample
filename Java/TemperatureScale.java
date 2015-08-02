public class TemperatureScale { 
	
	public static final TemperatureScale CELSIUS = new TemperatureScale();
	public static final TemperatureScale FAHRENHEIT = new TemperatureScale();

	private TemperatureScale() {}

	public boolean equals(TemperatureScale other) {
		return this == other;
	}
}
