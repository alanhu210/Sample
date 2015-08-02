import java.math.BigDecimal;

public class TemperatureMonitoringSystem {

	public static void main(String[] args) {

		Thermometer thermometer = new Thermometer();

		Person alex = new Person("Alex");
		Person ann = new Person("Ann");

		Threshold freezing = new Threshold(new Temperature(TemperatureScale.CELSIUS, new BigDecimal(0.0)));
		Threshold freezing2 = new Threshold(new Temperature(TemperatureScale.CELSIUS, new BigDecimal(0.0)), new BigDecimal(0.5));

		alex.setThreshold(freezing);
		ann.setThreshold(freezing2);

		System.out.println("Alex has a freezing threshold with no margin");
		System.out.println("Ann has a freezing threshold with margin of 0.5");

		thermometer.addTemperatureWatcher(alex);
		thermometer.addTemperatureWatcher(ann);

		double[] readings = getReadings();

		for (int i = 0; i < readings.length; i++) {
			thermometer.setTemperatureReading(readings[i]);
		}
	}

	private static double[] getReadings() {
		return new double[] {
			0.5,
			1.0,
			0.5,
			0.0,
			-0.5,
			0.0,
			-0.5,
			0.0,
			0.5,
			0.0
		};
	}
}
